<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayFeeResource\Pages;
use App\Models\PaymentGatewayFee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

class PaymentGatewayFeeResource extends Resource
{
    protected static ?string $model = PaymentGatewayFee::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Super Admin';
    protected static ?string $navigationLabel = 'Daftar Fee Transaksi';
    protected static ?string $modelLabel = 'Fee Transaksi';
    protected static ?string $pluralModelLabel = 'Daftar Fee Transaksi';
    protected static ?string $slug = 'daftar-fee-transaksi';
    protected static ?int $navigationSort = 9;

    // Disembunyikan dari navigasi — dikelola melalui menu "Xendit Webhook"
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('app_name')
                    ->label('Nama Aplikasi Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Contoh: Aplikasi Join PPAB'),

                Forms\Components\Select::make('app_id')
                    ->label('ID Aplikasi (Domain)')
                    ->options(function () {
                        $options = [];
                        $path = '/www/wwwroot';
                        if (File::exists($path)) {
                            $directories = File::directories($path);
                            foreach ($directories as $dir) {
                                $basename = basename($dir);
                                if (str_ends_with($basename, '.web.id') || str_ends_with($basename, '.or.id')) {
                                    $id = explode('.', $basename)[0];
                                    $options[$id] = $basename . ' (' . $id . ')';
                                }
                            }
                        }
                        return $options;
                    })
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Pilih dari daftar domain di server, atau ketik manual ID-nya jika tidak ada.')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('app_id_manual')
                            ->label('Ketik ID Aplikasi Manual')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return $data['app_id_manual'];
                    }),

                Forms\Components\TextInput::make('sysdev_fee')
                    ->label('Nominal Fee Sistem (Rp)')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),

                Forms\Components\TextInput::make('va_fee')
                    ->label('Fee VA')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('qr_fee')
                    ->label('Fee QRIS')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('outlet_fee')
                    ->label('Fee Outlet')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('ppn')
                    ->label('PPN (%)')
                    ->numeric()
                    ->default(0),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('app_name')
                    ->label('Nama Aplikasi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('app_id')
                    ->label('ID Aplikasi')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('sysdev_fee')
                    ->label('Fee Sistem')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ManagePaymentGatewayFees::route('/'),
        ];
    }
}
