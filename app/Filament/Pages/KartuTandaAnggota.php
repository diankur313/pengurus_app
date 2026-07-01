<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use App\Models\MemberLama;
use ZipArchive;
use Illuminate\Support\Facades\File;

class KartuTandaAnggota extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    public array $selectedRows = [];
    public string $selectedAngkatan = '';

    public function updatedSelectedRows()
    {
        $this->selectedAngkatan = '';
    }

    public function updatedSelectedAngkatan()
    {
        if (!empty($this->selectedAngkatan)) {
            $ids = MemberLama::where('member_nama_angkatan', $this->selectedAngkatan)
                             ->pluck('member_no')
                             ->toArray();
            $this->selectedRows = array_map('strval', $ids);
        } else {
            $this->selectedRows = [];
        }
    }

    public function getAngkatanOptionsProperty()
    {
        return MemberLama::query()
            ->whereNotNull('member_nama_angkatan')
            ->where('member_nama_angkatan', '!=', '')
            ->distinct()
            ->pluck('member_nama_angkatan')
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
            $user = MemberLama::where('member_no', $this->selectedRows[0])->first();
            if (!$user) return;

            $nama     = (string) $user->member_name;
            $angkatan = (string) $user->member_nama_angkatan;
            $nomor    = (string) $user->member_no;

            $imageContent = generateKtaImageContent($nama, $angkatan, $nomor);
            $filename = 'KTA_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nama) . '.png';

            return response()->streamDownload(function () use ($imageContent) {
                echo $imageContent;
            }, $filename, ['Content-Type' => 'image/png']);
        }

        $users = null;

        if (count($this->selectedRows) > 0) {
            $users = MemberLama::whereIn('member_no', $this->selectedRows)->get();
        } elseif (!empty($this->selectedAngkatan)) {
            $users = MemberLama::where('member_nama_angkatan', $this->selectedAngkatan)->get();
        }

        if (!$users || $users->isEmpty()) {
            return;
        }

        $zipFileName = 'KTA_Batch_' . date('Ymd_His') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($users as $user) {
                $nama     = (string) $user->member_name;
                $angkatan = (string) $user->member_nama_angkatan;
                $nomor    = (string) $user->member_no;

                $imageContent = generateKtaImageContent($nama, $angkatan, $nomor);
                $filename = 'KTA_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nama) . '.png';
                
                $zip->addFromString($filename, $imageContent);
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon  = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Kartu Tanda Anggota';
    protected static ?string $title           = 'Kartu Tanda Anggota';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.pages.kartu-tanda-anggota';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->getKey();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MemberLama::query()->whereNotNull('member_no')->where('member_no', '!=', ''))
            ->columns([
                ViewColumn::make('select')
                    ->label('')
                    ->view('filament.tables.columns.checkbox'),
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . profilePhotoUrl($record->photo, $record->member_name) . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ])
                    ->defaultImageUrl(fn ($record) => profilePhotoUrl(null, $record->member_name)),
                TextColumn::make('member_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_gend')
                    ->label('Gender')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_emai')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_nama_angkatan')
                    ->label('Angkatan')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('member_card')
                    ->label('Kartu Anggota')
                    ->getStateUsing(fn () => asset('kta_raw.png'))
                    ->width(120)
                    ->height(75)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "window.location.href='" . url('/kta-download/lama/' . $record->member_no) . "';",
                        'style'      => 'cursor: pointer; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: transform 0.15s; display: block;',
                        'title'      => 'Klik untuk download KTA',
                        'x-on:mouseenter' => "\$el.style.transform='scale(1.05)'",
                        'x-on:mouseleave' => "\$el.style.transform='scale(1)'",
                    ]),
            ])
            ->striped();
    }
}
