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
use Filament\Infolists\Components\ViewEntry;
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
                            ->label('')
                            ->getStateUsing(fn ($record) => filled($record->photo) && $record->photo !== 'avatar.png' ? profilePhotoUrl($record->photo, $record->name) : profilePhotoUrl(null, $record->name))
                            ->circular()
                            ->size(80)
                            ->columnSpanFull(),
                        ViewEntry::make('informasi_utama')
                            ->label('')
                            ->view('filament.infolists.ppab-row-box')
                            ->columnSpanFull()
                            ->getStateUsing(fn ($record) => [
                                'record' => $record,
                                'rows' => [
                                    [
                                        ['label' => 'Nama Lengkap', 'key' => 'name'],
                                        ['label' => 'Email', 'key' => 'email'],
                                    ],
                                    [
                                        ['label' => 'No. WhatsApp', 'key' => 'whatsapp'],
                                        ['label' => 'Gender', 'key' => 'gender', 'format' => 'ucfirst'],
                                    ],
                                    [
                                        ['label' => 'Tanggal Lahir', 'key' => 'tgl_lahir', 'format' => 'date'],
                                        ['label' => 'Umur (Tahun)', 'key' => 'realage'],
                                    ],
                                    [
                                        ['label' => 'Golongan Darah', 'key' => 'goldar'],
                                        ['label' => 'Status Pekerjaan', 'key' => 'sudah_bekerja', 'format' => 'sudah_bekerja'],
                                    ],
                                    [
                                        ['label' => 'Status Akun', 'key' => 'status'],
                                        ['label' => 'Nomor KTP', 'key' => 'ktp'],
                                    ],
                                ],
                            ]),
                    ]),

                Section::make('Detail Angkatan & Registrasi')
                    ->schema([
                        ViewEntry::make('detail_angkatan')
                            ->label('')
                            ->view('filament.infolists.ppab-row-box')
                            ->columnSpanFull()
                            ->getStateUsing(fn ($record) => [
                                'record' => $record,
                                'rows' => [
                                    [
                                        ['label' => 'Angkatan', 'key' => 'angkatan'],
                                        ['label' => 'Nama Angkatan', 'key' => 'nama_angkatan', 'format' => 'nama_angkatan'],
                                    ],
                                    [
                                        ['label' => 'Tahap / Status Pembayaran', 'key' => 'stage', 'format' => 'stage_badge'],
                                        ['label' => 'Paket / Tiket', 'key' => 'paket'],
                                    ],
                                    [
                                        ['label' => 'Sumber Informasi', 'key' => 'referral_source'],
                                        ['label' => 'Detail Sumber Informasi', 'key' => 'referral_source_detail'],
                                    ],
                                    [
                                        ['label' => 'Tanggal Registrasi', 'key' => 'created_at', 'format' => 'datetime'],
                                    ],
                                ],
                            ]),
                    ]),

                Section::make('Alamat KTP (TTL)')
                    ->schema([
                        ViewEntry::make('alamat_ktp')
                            ->label('')
                            ->view('filament.infolists.ppab-row-box')
                            ->columnSpanFull()
                            ->getStateUsing(fn ($record) => [
                                'record' => $record,
                                'rows' => [
                                    [
                                        ['label' => 'Alamat Lengkap KTP', 'key' => 'ttl_alamat', 'span' => 'full'],
                                    ],
                                    [
                                        ['label' => 'Provinsi KTP', 'key' => 'ttl_provinsi', 'format' => 'region:provinsi'],
                                        ['label' => 'Kota/Kabupaten KTP', 'key' => 'ttl_kota', 'format' => 'region:kabupaten'],
                                    ],
                                    [
                                        ['label' => 'Kecamatan KTP', 'key' => 'ttl_kecamatan', 'format' => 'region:kecamatan'],
                                        ['label' => 'Kelurahan KTP', 'key' => 'ttl_kelurahan', 'format' => 'region:kelurahan'],
                                    ],
                                    [
                                        ['label' => 'Kode Pos KTP', 'key' => 'ttl_kode_pos'],
                                    ],
                                ],
                            ]),
                    ]),

                Section::make('Alamat Domisili')
                    ->schema([
                        ViewEntry::make('alamat_domisili')
                            ->label('')
                            ->view('filament.infolists.ppab-row-box')
                            ->columnSpanFull()
                            ->getStateUsing(fn ($record) => [
                                'record' => $record,
                                'rows' => [
                                    [
                                        ['label' => 'Sama dengan Alamat KTP?', 'key' => 'sama_dengan', 'format' => 'bool_ya_tidak', 'span' => 'full'],
                                    ],
                                    [
                                        ['label' => 'Alamat Lengkap Domisili', 'key' => 'domisili_alamat', 'span' => 'full'],
                                    ],
                                    [
                                        ['label' => 'Provinsi Domisili', 'key' => 'domisili_provinsi', 'format' => 'region:provinsi'],
                                        ['label' => 'Kota/Kabupaten Domisili', 'key' => 'domisili_kota', 'format' => 'region:kabupaten'],
                                    ],
                                    [
                                        ['label' => 'Kecamatan Domisili', 'key' => 'domisili_kecamatan', 'format' => 'region:kecamatan'],
                                        ['label' => 'Kelurahan Domisili', 'key' => 'domisili_kelurahan', 'format' => 'region:kelurahan'],
                                    ],
                                    [
                                        ['label' => 'Kode Pos Domisili', 'key' => 'domisili_kode_pos'],
                                    ],
                                ],
                            ]),
                    ]),

                Section::make('Riwayat Pendidikan')
                    ->schema([
                        RepeatableEntry::make('education')
                            ->label('')
                            ->contained(true)
                            ->getStateUsing(function ($record) {
                                $rows = \Illuminate\Support\Facades\DB::connection('ppab')
                                    ->table('ppab_member_pendidikan')
                                    ->where('id_member', $record->id)
                                    ->get();

                                return $rows->map(function ($row) {
                                    $row = (array) $row;

                                    // Jika jenjang universitas, resolve ID ke nama dari ppab_master_universitas
                                    if ($row['jenjang'] === 'universitas' && is_numeric($row['institusi'])) {
                                        $univ = \Illuminate\Support\Facades\DB::connection('ppab')
                                            ->table('ppab_master_universitas')
                                            ->find((int) $row['institusi']);
                                        $row['institusi'] = $univ ? $univ->universitas : $row['institusi'];
                                    }

                                    return $row;
                                })->toArray();
                            })
                            ->schema([
                                TextEntry::make('institusi')->label('Institusi'),
                                TextEntry::make('jenjang')->label('Jenjang')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                                TextEntry::make('strata')->label('Strata/Jurusan')->formatStateUsing(fn ($state) => $state ? strtoupper((string) $state) : '-')->placeholder('-'),
                                TextEntry::make('masuk')->label('Tahun Masuk'),
                                TextEntry::make('keluar')->label('Tahun Keluar')->placeholder('-'),
                            ])
                            ->columns(5)
                            ->columnSpanFull()

                    ])
                    ->collapsible(),

                Section::make('Riwayat Pekerjaan')
                    ->schema([
                        RepeatableEntry::make('work')
                            ->label('')
                            ->contained(true)
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')
                                ->table('ppab_member_work')
                                ->where('id_member', $record->id)
                                ->get()
                                ->map(fn($r) => (array) $r)
                                ->toArray())
                            ->schema([
                                TextEntry::make('perusahaan')->label('Perusahaan'),
                                TextEntry::make('jabatan')->label('Jabatan'),
                                TextEntry::make('negara')
                                    ->label('Negara')
                                    ->formatStateUsing(fn ($state) => self::getCountryName($state)),
                                TextEntry::make('industri')->label('Sektor Industri')->placeholder('-'),
                                TextEntry::make('tipe')->label('Tipe Pekerjaan')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                                TextEntry::make('masih_kerja')->label('Status Aktif')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                                TextEntry::make('kerja_dari')->label('Mulai Bekerja'),
                                TextEntry::make('kerja_sampai')->label('Selesai Bekerja')->placeholder('-'),
                                TextEntry::make('lokasi_kerja')
                                    ->label('Lokasi Kerja')
                                    ->formatStateUsing(fn ($state) => self::getRegionName('kabupaten', $state)),
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
                            ->contained(true)
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')
                                ->table('ppab_member_skills')
                                ->leftJoin('skills', 'ppab_member_skills.id_skill', '=', 'skills.id')
                                ->where('ppab_member_skills.id_member', $record->id)
                                ->select('ppab_member_skills.*', 'skills.skill as skill_name')
                                ->get()
                                ->map(fn($r) => (array) $r)
                                ->toArray())
                            ->schema([
                                TextEntry::make('skill_name')->label('Nama Keahlian'),
                                TextEntry::make('skill_level')->label('Level Keahlian')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
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
                            ->contained(true)
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')
                                ->table('ppab_member_penyakit')
                                ->where('id_member', $record->id)
                                ->get()
                                ->map(fn($r) => (array) $r)
                                ->toArray())
                            ->schema([
                                TextEntry::make('riwayat_penyakit')->label('Nama Penyakit'),
                                TextEntry::make('riwayat_penyakit_tingkat')->label('Tingkat Keparahan')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        RepeatableEntry::make('penyakit_special')
                            ->label('Kondisi Medis Khusus / Kebutuhan Khusus')
                            ->contained(true)
                            ->getStateUsing(fn ($record) => \Illuminate\Support\Facades\DB::connection('ppab')
                                ->table('ppab_member_penyakit_special')
                                ->where('id_member', $record->id)
                                ->get()
                                ->map(fn($r) => (array) $r)
                                ->toArray())
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
            ->recordAction(null)
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
                TextColumn::make('paket')
                    ->label('Paket')
                    ->formatStateUsing(fn($state) => filled($state) ? strtoupper($state) : '-')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_type')
                    ->label('Tipe Pembayaran')
                    ->getStateUsing(function ($record) {
                        $payment = \App\Models\PpabPayment::where('id_member', $record->uuid)
                            ->orderByRaw("FIELD(status, 'PAID', 'PENDING', 'EXPIRED') ASC")
                            ->latest()
                            ->first();

                        if (!$payment) {
                            return '-';
                        }

                        if ($payment->early_bird == 1) {
                            return 'Early Bird';
                        }

                        if ($payment->payment_type === 'dp') {
                            return 'Down Payment';
                        }

                        $note = $payment->note ?? '';
                        if (stripos($note, 'Early Bird') !== false) {
                            return 'Early Bird';
                        }
                        if (stripos($note, 'Down Payment') !== false) {
                            return 'Down Payment';
                        }
                        if (stripos($note, 'Bundling 2') !== false || stripos($note, 'b2') !== false || stripos($note, '2 Member') !== false) {
                            return 'Bundling 2 Members';
                        }
                        if (stripos($note, 'Bundling 3') !== false || stripos($note, 'b3') !== false || stripos($note, '3 Member') !== false) {
                            return 'Bundling 3 Members';
                        }
                        if (stripos($note, 'Full Payment') !== false) {
                            return 'Full Payment';
                        }

                        if ($payment->payment_type === 'full') {
                            return 'Full Payment';
                        }

                        return ucfirst($payment->payment_type ?? '-');
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Early Bird' => 'success',
                        'Full Payment' => 'info',
                        'Down Payment' => 'warning',
                        'Bundling 2 Members' => 'primary',
                        'Bundling 3 Members' => 'danger',
                        default => 'gray'
                    }),
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
