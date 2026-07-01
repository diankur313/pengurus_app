<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpabParticipantResource\Pages;
use App\Models\PpabParticipant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\Builder;

class PpabParticipantResource extends Resource
{
    protected static ?string $model = PpabParticipant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'PPAB';
    protected static ?string $navigationLabel = 'Peserta';
    protected static ?string $modelLabel = 'Peserta PPAB';
    protected static ?string $pluralModelLabel = 'Peserta PPAB';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Tambahkan field form di sini
        ]);
    }

    protected static function getRegionName(string $table, ?string $id): string
    {
        if (!$id) {
            return '-';
        }
        try {
            $record = \Illuminate\Support\Facades\DB::connection('ppab')
                ->table($table)
                ->where('id', $id)
                ->first();
            
            return $record ? ($record->$table ?? $id) : $id;
        } catch (\Exception $e) {
            return $id;
        }
    }

    protected static function getCountryName(?int $id): string
    {
        if (!$id) {
            return '-';
        }
        try {
            $record = \Illuminate\Support\Facades\DB::connection('ppab')
                ->table('countries')
                ->where('id', $id)
                ->first();
            return $record ? ($record->country ?? $id) : $id;
        } catch (\Exception $e) {
            return $id;
        }
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Utama')
                    ->schema([
                        ImageEntry::make('photo')
                            ->label('Photo')
                            ->getStateUsing(fn ($record) => filled($record->photo) && $record->photo !== 'avatar.png' ? profilePhotoUrl($record->photo, $record->name) : profilePhotoUrl(null, $record->name))
                            ->circular()
                            ->columnSpanFull(),
                        TextEntry::make('name')->label('Nama Lengkap'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('whatsapp')->label('No. WhatsApp'),
                        TextEntry::make('gender')
                            ->label('Gender')
                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                        TextEntry::make('tgl_lahir')
                            ->label('Tanggal Lahir')
                            ->date('d F Y'),
                        TextEntry::make('realage')->label('Umur (Tahun)'),
                        TextEntry::make('goldar')->label('Golongan Darah'),
                        TextEntry::make('sudah_bekerja')
                            ->label('Status Pekerjaan')
                            ->formatStateUsing(fn ($state) => $state ? 'Sudah Bekerja' : 'Belum Bekerja'),
                        TextEntry::make('status')
                            ->label('Status Akun'),
                        TextEntry::make('ktp')
                            ->label('Nomor KTP'),
                    ])
                    ->columns(2),

                Section::make('Detail Angkatan & Registrasi')
                    ->schema([
                        TextEntry::make('angkatan')
                            ->label('Angkatan'),
                        TextEntry::make('nama_angkatan')
                            ->label('Nama Angkatan')
                            ->getStateUsing(function ($record) {
                                if (!$record->nama_angkatan) return '-';
                                try {
                                    $angkatanDetail = \Illuminate\Support\Facades\DB::connection('ppab')
                                        ->table('ppab_nama_angkatans')
                                        ->where('nama_angkatan', $record->nama_angkatan)
                                        ->first();
                                    if ($angkatanDetail && $angkatanDetail->tahun) {
                                        return "{$record->nama_angkatan} ({$angkatanDetail->tahun})";
                                    }
                                } catch (\Exception $e) {
                                    // ignore
                                }
                                return $record->nama_angkatan;
                            }),
                        TextEntry::make('stage')
                            ->label('Tahap / Status Pembayaran')
                            ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'paid_payment' => 'success',
                                'pending_payment' => 'warning',
                                'expired_payment' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('paket')
                            ->label('Paket / Tiket'),
                        TextEntry::make('referral_source')
                            ->label('Sumber Informasi'),
                        TextEntry::make('referral_source_detail')
                            ->label('Detail Sumber Informasi'),
                        TextEntry::make('created_at')
                            ->label('Tanggal Registrasi')
                            ->dateTime('d F Y H:i:s'),
                    ])
                    ->columns(2),

                Section::make('Alamat KTP (TTL)')
                    ->schema([
                        TextEntry::make('ttl_alamat')
                            ->label('Alamat Lengkap KTP')
                            ->columnSpanFull(),
                        TextEntry::make('ttl_provinsi')
                            ->label('Provinsi KTP')
                            ->getStateUsing(fn ($record) => self::getRegionName('provinsi', $record->ttl_provinsi)),
                        TextEntry::make('ttl_kota')
                            ->label('Kota/Kabupaten KTP')
                            ->getStateUsing(fn ($record) => self::getRegionName('kabupaten', $record->ttl_kota)),
                        TextEntry::make('ttl_kecamatan')
                            ->label('Kecamatan KTP')
                            ->getStateUsing(fn ($record) => self::getRegionName('kecamatan', $record->ttl_kecamatan)),
                        TextEntry::make('ttl_kelurahan')
                            ->label('Kelurahan KTP')
                            ->getStateUsing(fn ($record) => self::getRegionName('kelurahan', $record->ttl_kelurahan)),
                        TextEntry::make('ttl_kode_pos')
                            ->label('Kode Pos KTP'),
                    ])
                    ->columns(2),

                Section::make('Alamat Domisili')
                    ->schema([
                        TextEntry::make('sama_dengan')
                            ->label('Sama dengan Alamat KTP?')
                            ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                            ->columnSpanFull(),
                        TextEntry::make('domisili_alamat')
                            ->label('Alamat Lengkap Domisili')
                            ->columnSpanFull(),
                        TextEntry::make('domisili_provinsi')
                            ->label('Provinsi Domisili')
                            ->getStateUsing(fn ($record) => self::getRegionName('provinsi', $record->domisili_provinsi)),
                        TextEntry::make('domisili_kota')
                            ->label('Kota/Kabupaten Domisili')
                            ->getStateUsing(fn ($record) => self::getRegionName('kabupaten', $record->domisili_kota)),
                        TextEntry::make('domisili_kecamatan')
                            ->label('Kecamatan Domisili')
                            ->getStateUsing(fn ($record) => self::getRegionName('kecamatan', $record->domisili_kecamatan)),
                        TextEntry::make('domisili_kelurahan')
                            ->label('Kelurahan Domisili')
                            ->getStateUsing(fn ($record) => self::getRegionName('kelurahan', $record->domisili_kelurahan)),
                        TextEntry::make('domisili_kode_pos')
                            ->label('Kode Pos Domisili'),
                    ])
                    ->columns(2),

                Section::make('Riwayat Pendidikan')
                    ->schema([
                        RepeatableEntry::make('education')
                            ->label('')
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_member_pendidikan')->where('id_member', $record->id_member)->get())
                            ->schema([
                                TextEntry::make('institusi')->label('Institusi'),
                                TextEntry::make('jenjang')->label('Jenjang')->formatStateUsing(fn ($state) => ucfirst($state)),
                                TextEntry::make('strata')->label('Strata/Jurusan')->formatStateUsing(fn ($state) => strtoupper($state)),
                                TextEntry::make('masuk')->label('Tahun Masuk'),
                                TextEntry::make('keluar')->label('Tahun Keluar'),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                    ])
                    ->collapsible(),

                Section::make('Riwayat Pekerjaan')
                    ->schema([
                        RepeatableEntry::make('work')
                            ->label('')
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_member_work')->where('id_member', $record->id_member)->get())
                            ->schema([
                                TextEntry::make('perusahaan')->label('Perusahaan'),
                                TextEntry::make('jabatan')->label('Jabatan'),
                                TextEntry::make('negara')
                                    ->label('Negara')
                                    ->getStateUsing(fn ($record) => self::getCountryName($record->negara)),
                                TextEntry::make('industri')->label('Sektor Industri')->placeholder('-'),
                                TextEntry::make('tipe')->label('Tipe Pekerjaan')->formatStateUsing(fn ($state) => ucfirst($state)),
                                TextEntry::make('masih_kerja')->label('Status Aktif')->formatStateUsing(fn ($state) => ucfirst($state)),
                                TextEntry::make('kerja_dari')->label('Mulai Bekerja'),
                                TextEntry::make('kerja_sampai')->label('Selesai Bekerja')->placeholder('-'),
                                TextEntry::make('lokasi_kerja')
                                    ->label('Lokasi Kerja')
                                    ->getStateUsing(fn ($record) => self::getRegionName('kabupaten', $record->lokasi_kerja)),
                                TextEntry::make('tipe_nakes')->label('Tipe Nakes')->placeholder('-'),
                                TextEntry::make('str_ready')->label('STR Ready')->placeholder('-'),
                                TextEntry::make('tipe_str')->label('Tipe STR')->placeholder('-'),
                                TextEntry::make('str_dari_tgl')->label('STR Berlaku Dari')->placeholder('-'),
                                TextEntry::make('str_sampai_tgl')->label('STR Berlaku Sampai')->placeholder('-'),
                                TextEntry::make('deskripsi_pekerjaan')->label('Deskripsi Pekerjaan')->columnSpanFull()->placeholder('-'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                    ])
                    ->collapsible(),

                Section::make('Keahlian / Skills')
                    ->schema([
                        RepeatableEntry::make('skills')
                            ->label('')
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')
                                ->table('ppab_member_skills')
                                ->leftJoin('skills', 'ppab_member_skills.id_skill', '=', 'skills.id')
                                ->where('ppab_member_skills.id_member', $record->id_member)
                                ->select('ppab_member_skills.*', 'skills.skill as skill_name')
                                ->get())
                            ->schema([
                                TextEntry::make('skill_name')->label('Nama Keahlian'),
                                TextEntry::make('skill_level')->label('Level Keahlian')->formatStateUsing(fn ($state) => ucfirst($state)),
                                TextEntry::make('skill_point')->label('Poin/Nilai Keahlian')->placeholder('-'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                    ])
                    ->collapsible(),

                Section::make('Kondisi Kesehatan')
                    ->schema([
                        RepeatableEntry::make('penyakit')
                            ->label('Daftar Penyakit')
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_member_penyakit')->where('id_member', $record->id_member)->get())
                            ->schema([
                                TextEntry::make('riwayat_penyakit')->label('Nama Penyakit'),
                                TextEntry::make('riwayat_penyakit_tingkat')->label('Tingkat Keparahan')->formatStateUsing(fn ($state) => ucfirst($state)),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        RepeatableEntry::make('penyakit_special')
                            ->label('Kondisi Medis Khusus / Kebutuhan Khusus')
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_member_penyakit_special')->where('id_member', $record->id_member)->get())
                            ->schema([
                                TextEntry::make('description')->label('Deskripsi Kondisi Khusus')->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(Tables\Actions\ViewAction::class)
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->extraAttributes(fn($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . profilePhotoUrl($record->photo, $record->name) . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ])
                    ->getStateUsing(
                        fn($record) => filled($record->photo) && $record->photo !== 'avatar.png'
                            ? profilePhotoUrl($record->photo, $record->name)
                            : null
                    )
                    ->defaultImageUrl(fn($record) => profilePhotoUrl(null, $record->name)),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\ViewColumn::make('whatsapp')
                    ->label('No. HP')
                    ->view('filament.tables.columns.whatsapp-link')
                    ->searchable(),
                TextColumn::make('stage')
                    ->label('Payment Status')
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid_payment' => 'success',
                        'pending_payment' => 'warning',
                        'expired_payment' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPpabParticipants::route('/'),
            'create' => Pages\CreatePpabParticipant::route('/create'),
            'view' => Pages\ViewPpabParticipant::route('/{record}'),
            'edit' => Pages\EditPpabParticipant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Selalu batasi hanya melihat peserta untuk angkatan = 1
        $query->where('angkatan', '1');

        return $query;
    }
}
