<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\DiscountCoupon;
use App\Models\Payment;
use App\Models\PaymentLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Pembayaran';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel      = 'Pembayaran';
    protected static ?string $pluralModelLabel = 'Pembayaran';
    protected static ?int    $navigationSort  = 6;

    // ─── PAYMENT FORM ────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('desc')
                    ->label('Deskripsi Penagihan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: SPP Bulan Juni 2026')
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('start')
                    ->label('Periode Mulai')
                    ->required()
                    ->native(false),

                Forms\Components\DatePicker::make('end')
                    ->label('Periode Akhir')
                    ->required()
                    ->native(false)
                    ->after('start'),
            ]),

            Forms\Components\Section::make('💳 Metode Pembayaran')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Toggle::make('va')
                            ->label('Virtual Account')
                            ->helperText('BNI, BSI, BRI, Mandiri, Permata')
                            ->default(false),

                        Forms\Components\Toggle::make('qris')
                            ->label('QRIS')
                            ->helperText('QR Code Payment')
                            ->default(false),

                        Forms\Components\Toggle::make('cs')
                            ->label('Convenience Store')
                            ->helperText('Indomaret')
                            ->default(false),
                    ]),
                ])
                ->compact(),

            Forms\Components\Section::make('💰 Nominal')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('amount_dasar')
                            ->label('Nominal Angkatan Dasar')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('amount_lanjutan')
                            ->label('Nominal Angkatan Lanjutan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),
                    ]),
                ])
                ->compact(),

            Forms\Components\Section::make('🔔 Reminder')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Toggle::make('send_reminder')
                            ->label('Kirim Reminder Email')
                            ->default(false)
                            ->live(),

                        Forms\Components\TextInput::make('reminder_days_before')
                            ->label('H- Sebelum Batas Akhir')
                            ->numeric()
                            ->suffix('hari')
                            ->placeholder('3')
                            ->visible(fn (Forms\Get $get) => (bool) $get('send_reminder')),
                    ]),
                ])
                ->compact()
                ->collapsible(),
        ]);
    }

    // ─── PAYMENT TABLE ───────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('desc')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('start')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end')
                    ->label('Batas Akhir')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('va')
                    ->label('VA')
                    ->boolean(),

                Tables\Columns\IconColumn::make('qris')
                    ->label('QRIS')
                    ->boolean(),

                Tables\Columns\IconColumn::make('cs')
                    ->label('CS')
                    ->boolean(),

                Tables\Columns\TextColumn::make('amount_dasar')
                    ->label('Dasar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_lanjutan')
                    ->label('Lanjutan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'gray'    => 'closed',
                        'warning' => 'PENDING',
                        'success' => 'PAID',
                        'danger'  => 'EXPIRED',
                        'info'    => 'BYPASS',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
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
            'index' => Pages\ManagePayments::route('/'),
        ];
    }
}
