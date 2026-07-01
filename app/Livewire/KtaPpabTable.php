<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use App\Models\MemberPpab;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use ZipArchive;
use Illuminate\Support\Facades\File;

class KtaPpabTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $selectedRows = [];
    public string $selectedAngkatan = '';

    public function updatedSelectedRows()
    {
        $this->selectedAngkatan = '';
    }

    public function updatedSelectedAngkatan()
    {
        if (!empty($this->selectedAngkatan)) {
            $ids = MemberPpab::where('nama_angkatan', $this->selectedAngkatan)
                             ->pluck('id_member')
                             ->toArray();
            $this->selectedRows = array_map('strval', $ids);
        } else {
            $this->selectedRows = [];
        }
    }

    public function getAngkatanOptionsProperty()
    {
        return MemberPpab::query()
            ->whereNotNull('nama_angkatan')
            ->where('nama_angkatan', '!=', '')
            ->distinct()
            ->pluck('nama_angkatan')
            ->toArray();
    }

    public function form(Form $form): Form
    {
        $options = array_combine($this->angkatanOptions, $this->angkatanOptions);
        
        return $form->schema([
            Select::make('selectedAngkatan')
                ->label('')
                ->hiddenLabel()
                ->options($options)
                ->searchable()
                ->live()
                ->placeholder('-- Select --')
                ->extraAttributes(['class' => 'min-w-[250px]'])
        ])->statePath('');
    }

    public function downloadSelected()
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');

        if (count($this->selectedRows) === 1 && empty($this->selectedAngkatan)) {
            // Unduh Tunggal
            $user = MemberPpab::where('id_member', $this->selectedRows[0])->first();
            if (!$user) return;

            $nama     = (string) $user->name;
            $angkatan = (string) $user->nama_angkatan;
            $nomor    = (string) $user->id_member;

            $imageContent = generateKtaImageContent($nama, $angkatan, $nomor);
            $filename = 'KTA_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nama) . '.png';

            return response()->streamDownload(function () use ($imageContent) {
                echo $imageContent;
            }, $filename, ['Content-Type' => 'image/png']);
        }

        $users = null;

        if (count($this->selectedRows) > 0) {
            $users = MemberPpab::whereIn('id_member', $this->selectedRows)->get();
        } elseif (!empty($this->selectedAngkatan)) {
            $users = MemberPpab::where('nama_angkatan', $this->selectedAngkatan)->get();
        }

        if (!$users || $users->isEmpty()) {
            return;
        }

        $zipFileName = 'KTA_Batch_PPAB_' . date('Ymd_His') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($users as $user) {
                $nama     = (string) $user->name;
                $angkatan = (string) $user->nama_angkatan;
                $nomor    = (string) $user->id_member;

                $imageContent = generateKtaImageContent($nama, $angkatan, $nomor);
                $filename = 'KTA_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nama) . '.png';
                
                $zip->addFromString($filename, $imageContent);
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MemberPpab::query())
            ->columns([
                ViewColumn::make('select')
                    ->label('')
                    ->view('filament.tables.columns.checkbox'),
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . profilePhotoUrl($record->photo, $record->name) . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ])
                    ->getStateUsing(fn ($record) => filled($record->photo) && $record->photo !== 'avatar.png'
                        ? profilePhotoUrl($record->photo, $record->name)
                        : null
                    )
                    ->defaultImageUrl(fn ($record) => profilePhotoUrl(null, $record->name)),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_angkatan')
                    ->label('Angkatan')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('member_card')
                    ->label('Kartu Anggota')
                    ->getStateUsing(fn () => asset('kta_raw.png'))
                    ->width(120)
                    ->height(75)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "window.location.href='" . url('/kta-download/ppab/' . $record->id_member) . "';",
                        'style'      => 'cursor: pointer; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: transform 0.15s; display: block;',
                        'title'      => 'Klik untuk download KTA',
                        'x-on:mouseenter' => "\$el.style.transform='scale(1.05)'",
                        'x-on:mouseleave' => "\$el.style.transform='scale(1)'",
                    ]),
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.kta-ppab-table');
    }
}
