<?php

namespace App\Filament\Pages;

use App\Exports\CivitasAngkatanExport;
use App\Models\MemberLama;
use App\Models\PpabNamaAngkatan;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class AllCivitas extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'All Civitas';
    protected static ?string $title = 'All Civitas';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.all-civitas';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->getKey();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MemberLama::query()->whereNotNull('member_no')->where('member_no', '!=', ''))
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . profilePhotoUrl($record->photo, $record->member_name) . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ])
                    ->defaultImageUrl(fn ($record) => profilePhotoUrl(null, $record->member_name)),
                TextColumn::make('member_no')
                    ->label('Member ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_gend')
                    ->label('Gender')
                    ->formatStateUsing(fn ($state) => match($state) { 'l' => 'Pria', 'p' => 'Wanita', default => ucfirst($state ?? '') })
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
            ])
            ->actions([
                Actions\EditAction::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading(fn ($record) => "Edit Data: {$record->member_name}")
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->form(fn () => static::getEditFormSchema())
                    ->using(function (MemberLama $record, array $data): bool {
                        $record->update($data);

                        Notification::make()
                            ->title('Data berhasil diperbarui')
                            ->body("Data {$record->member_name} telah diupdate.")
                            ->success()
                            ->send();

                        return true;
                    }),
                Actions\DeleteAction::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading(fn ($record) => "Hapus Data: {$record->member_name}")
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalDescription('Data civitas akan dihapus permanen dari database member lama. Tindakan ini tidak dapat dibatalkan.')
                    ->successNotificationTitle('Data civitas berhasil dihapus'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Hapus Data Civitas Terpilih')
                        ->modalDescription('Semua data civitas yang dipilih akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->successNotificationTitle('Data civitas terpilih berhasil dihapus'),
                ]),
            ]);
    }

    public static function getEditFormSchema(): array
    {
        return [
            TextInput::make('member_no')
                ->label('Member ID')
                ->required(),
            TextInput::make('member_name')
                ->label('Nama Lengkap')
                ->required(),
            TextInput::make('member_nick')
                ->label('Nama Panggilan'),
            Select::make('member_gend')
                ->label('Gender')
                ->options([
                    'l' => 'Pria',
                    'p' => 'Wanita',
                ])
                ->default('l'),
            TextInput::make('member_emai')
                ->label('Email')
                ->email(),
            TextInput::make('member_hp')
                ->label('No. HP'),
            TextInput::make('member_wa')
                ->label('No. WhatsApp'),
            TextInput::make('member_pob')
                ->label('Tempat Lahir'),
            TextInput::make('member_dob')
                ->label('Tanggal Lahir'),
            TextInput::make('member_alam')
                ->label('Alamat'),
            TextInput::make('member_kota')
                ->label('Kota'),
            TextInput::make('member_job')
                ->label('Pekerjaan'),
            TextInput::make('member_nama_angkatan')
                ->label('Angkatan'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Select::make('mode')
                        ->label('Mode Angkatan')
                        ->options([
                            'range' => 'Rentang Angkatan',
                            'single' => 'Angkatan Tunggal',
                        ])
                        ->default('range')
                        ->live()
                        ->required(),
                    Select::make('angkatan_awal')
                        ->label('Angkatan Awal')
                        ->options(fn () => collect()
                            ->merge(
                                MemberLama::query()
                                    ->whereNotNull('member_nama_angkatan')
                                    ->where('member_nama_angkatan', '!=', '')
                                    ->distinct()
                                    ->pluck('member_nama_angkatan')
                            )
                            ->merge(
                                PpabNamaAngkatan::query()
                                    ->whereNotNull('nama_angkatan')
                                    ->where('nama_angkatan', '!=', '')
                                    ->pluck('nama_angkatan')
                            )
                            ->unique()
                            ->sort()
                            ->values()
                            ->mapWithKeys(fn ($v) => [$v => $v])
                            ->toArray())
                        ->searchable()
                        ->visible(fn (Get $get) => $get('mode') === 'range')
                        ->required(),
                    Select::make('angkatan_akhir')
                        ->label('Angkatan Akhir')
                        ->options(fn () => collect()
                            ->merge(
                                MemberLama::query()
                                    ->whereNotNull('member_nama_angkatan')
                                    ->where('member_nama_angkatan', '!=', '')
                                    ->distinct()
                                    ->pluck('member_nama_angkatan')
                            )
                            ->merge(
                                PpabNamaAngkatan::query()
                                    ->whereNotNull('nama_angkatan')
                                    ->where('nama_angkatan', '!=', '')
                                    ->pluck('nama_angkatan')
                            )
                            ->unique()
                            ->sort()
                            ->values()
                            ->mapWithKeys(fn ($v) => [$v => $v])
                            ->toArray())
                        ->searchable()
                        ->visible(fn (Get $get) => $get('mode') === 'range')
                        ->required(),
                    Select::make('angkatan')
                        ->label('Angkatan')
                        ->options(fn () => collect()
                            ->merge(
                                MemberLama::query()
                                    ->whereNotNull('member_nama_angkatan')
                                    ->where('member_nama_angkatan', '!=', '')
                                    ->distinct()
                                    ->pluck('member_nama_angkatan')
                            )
                            ->merge(
                                PpabNamaAngkatan::query()
                                    ->whereNotNull('nama_angkatan')
                                    ->where('nama_angkatan', '!=', '')
                                    ->pluck('nama_angkatan')
                            )
                            ->unique()
                            ->sort()
                            ->values()
                            ->mapWithKeys(fn ($v) => [$v => $v])
                            ->toArray())
                        ->searchable()
                        ->visible(fn (Get $get) => $get('mode') === 'single')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $query = MemberLama::query()
                        ->whereNotNull('member_no')
                        ->where('member_no', '!=', '');

                    if ($data['mode'] === 'range') {
                        $query->whereBetween('member_nama_angkatan', [$data['angkatan_awal'], $data['angkatan_akhir']]);
                    } else {
                        $query->where('member_nama_angkatan', $data['angkatan']);
                    }

                    return Excel::download(
                        new CivitasAngkatanExport($query),
                        'civitas_' . now()->format('Ymd_His') . '.xlsx'
                    );
                }),
        ];
    }
}
