<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PpabPaymentResource\Pages;
use App\Models\PpabPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PpabPaymentResource extends Resource
{
    protected static ?string $model = PpabPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'PPAB';
    protected static ?string $navigationLabel = 'Pembayaran';
    protected static ?string $modelLabel = 'Pembayaran PPAB';
    protected static ?string $pluralModelLabel = 'Pembayaran PPAB';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Tambahkan field form di sini
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Channel')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Payment Type')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ref_code')
                    ->label('Ref. Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Disc.')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PAID', 'SETTLED', 'COMPLETED', 'SUCCEEDED' => 'success',
                        'PENDING', 'WAITING', 'SETTLING' => 'warning',
                        'EXPIRED' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('angkatan')
                    ->label('Filter by Angkatan')
                    ->options(fn () => \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_nama_angkatans')->pluck('nama_angkatan', 'id')->toArray())
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('member', function ($q) use ($data) {
                            $q->where('angkatan', $data['value']);
                        });
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
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
            'index' => Pages\ListPpabPayments::route('/'),
            'create' => Pages\CreatePpabPayment::route('/create'),
            'edit' => Pages\EditPpabPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Jika bukan super_admin, batasi hanya melihat transaksi untuk angkatan = 1
        if (! auth()->user()->hasRole('super_admin')) {
            $query->whereHas('member', function ($q) {
                $q->where('angkatan', '1');
            });
        }

        return $query;
    }
}
