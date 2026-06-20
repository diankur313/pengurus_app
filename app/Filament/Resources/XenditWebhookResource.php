<?php

namespace App\Filament\Resources;

use App\Filament\Resources\XenditWebhookResource\Pages;
use App\Models\PaymentGatewayFee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

class XenditWebhookResource extends Resource
{
    protected static ?string $model = PaymentGatewayFee::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationLabel = 'Xendit Webhook';
    protected static ?string $modelLabel = 'Konfigurasi Xendit';
    protected static ?string $pluralModelLabel = 'Xendit Webhook';
    protected static ?string $slug = 'xendit-webhook';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Aplikasi')
                    ->schema([
                        Forms\Components\TextInput::make('app_name')
                            ->label('Nama Aplikasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contoh: Join PPAB')
                            ->helperText('Nama lengkap aplikasi untuk identifikasi.'),

                        Forms\Components\Select::make('app_id')
                            ->label('ID Aplikasi (Domain)')
                            ->options(function () {
                                $options = [];
                                $path = '/www/wwwroot';
                                if (File::exists($path)) {
                                    foreach (File::directories($path) as $dir) {
                                        $basename = basename($dir);
                                        if (str_ends_with($basename, '.web.id') || str_ends_with($basename, '.or.id')) {
                                            $id = explode('.', $basename)[0];
                                            $options[$id] = $basename . ' → ' . $id;
                                        }
                                    }
                                }
                                return $options;
                            })
                            ->searchable()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) {
                                    $set('internal_webhook_url', null);
                                    return;
                                }
                                // Cari full domain dari directory listing berdasarkan app_id yang dipilih
                                $fullDomain = null;
                                foreach (File::directories('/www/wwwroot') as $dir) {
                                    $basename = basename($dir);
                                    $id = explode('.', $basename)[0];
                                    if ($id === $state) {
                                        $fullDomain = $basename;
                                        break;
                                    }
                                }
                                if ($fullDomain) {
                                    $set('internal_webhook_url', "https://{$fullDomain}/api/internal/webhook/invoice");
                                }
                            })
                            ->helperText('Prefix yang digunakan di external_id Xendit, contoh: join-ppab → prefix PPAB'),
                    ])->columns(2),

                Forms\Components\Section::make('Mode Xendit')
                    ->description('Tentukan apakah aplikasi ini menggunakan kredensial Xendit development atau production.')
                    ->schema([
                        Forms\Components\ToggleButtons::make('mode')
                            ->label('Environment')
                            ->options([
                                'development' => 'Development',
                                'production'  => 'Production',
                            ])
                            ->icons([
                                'development' => 'heroicon-o-beaker',
                                'production'  => 'heroicon-o-rocket-launch',
                            ])
                            ->colors([
                                'development' => 'warning',
                                'production'  => 'success',
                            ])
                            ->inline()
                            ->required()
                            ->default('development'),
                    ]),

                Forms\Components\Section::make('Internal Webhook URL')
                    ->description('Diisi otomatis dari pilihan domain di atas. Tidak perlu diisi manual.')
                    ->schema([
                        Forms\Components\TextInput::make('internal_webhook_url')
                            ->label('URL Internal Webhook (Auto)')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Pilih App ID di atas untuk mengisi otomatis...')
                            ->helperText('📌 Readonly — format: https://{domain}/api/internal/webhook/invoice')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Konfigurasi Fee')
                    ->description('Fee yang akan dipotong saat pembayaran diterima. Fee PG (Xendit) dihitung otomatis berdasarkan metode pembayaran.')
                    ->schema([
                        Forms\Components\TextInput::make('sysdev_fee')
                            ->label('Fee Sysdev (Rp)')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\TextInput::make('va_fee')
                            ->label('Fee VA / Bank Transfer')
                            ->numeric()
                            ->default(4000)
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('qr_fee')
                            ->label('Fee QRIS (%)')
                            ->numeric()
                            ->default(0.7)
                            ->suffix('%'),

                        Forms\Components\TextInput::make('outlet_fee')
                            ->label('Fee Retail Outlet (Rp)')
                            ->numeric()
                            ->default(5000)
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('ppn')
                            ->label('PPN (%)')
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('app_name')
                    ->label('Aplikasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('app_id')
                    ->label('App ID')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production'  => 'success',
                        'development' => 'warning',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->icon(fn (string $state): string => match ($state) {
                        'production'  => 'heroicon-o-rocket-launch',
                        'development' => 'heroicon-o-beaker',
                        default       => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('internal_webhook_url')
                    ->label('Webhook URL')
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->internal_webhook_url)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sysdev_fee')
                    ->label('Fee Sysdev')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mode')
                    ->label('Mode')
                    ->options([
                        'development' => 'Development',
                        'production'  => 'Production',
                    ]),
            ])
            ->actions([
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
            'index'  => Pages\ListXenditWebhooks::route('/'),
            'create' => Pages\CreateXenditWebhook::route('/create'),
            'edit'   => Pages\EditXenditWebhook::route('/{record}/edit'),
        ];
    }
}
