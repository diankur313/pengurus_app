<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CivitasResource\Pages;
use App\Models\CivitasPendidikan;
use App\Models\MemberPpab;
use App\Models\MemberLama;
use App\Jobs\SendPortalInviteJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

class CivitasResource extends Resource
{
    protected static ?string $model = CivitasPendidikan::class;

    protected static ?string $slug = 'civitas';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Daftar Civitas';
    protected static ?string $navigationGroup = 'Pendidikan';
    protected static ?string $modelLabel = 'Daftar Civitas';
    protected static ?string $pluralModelLabel = 'Daftar Civitas';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_civitas');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_civitas');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('update_civitas');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_civitas');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where(function ($q) {
                $q->where('source_type', 'table_member_lama')
                    ->orWhere(function ($q2) {
                        $q2->where('source_type', 'table_ppab_baru')
                            ->whereIn('source_id', MemberPpab::where('stage', 'paid_payment')->pluck('id_member'));
                    });
            }))
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
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where(function ($q1) use ($search) {
                                $q1->where('source_type', 'table_ppab_baru')
                                    ->whereIn('source_id', MemberPpab::where('name', 'like', "%{$search}%")->pluck('id_member'));
                            })->orWhere(function ($q2) use ($search) {
                                $q2->where('source_type', 'table_member_lama')
                                    ->whereIn('source_id', MemberLama::where('member_name', 'like', "%{$search}%")->pluck('member_no'));
                            });
                        });
                    }),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where(function ($q1) use ($search) {
                                $q1->where('source_type', 'table_ppab_baru')
                                    ->whereIn('source_id', MemberPpab::where('gender', 'like', "%{$search}%")->pluck('id_member'));
                            })->orWhere(function ($q2) use ($search) {
                                $q2->where('source_type', 'table_member_lama')
                                    ->whereIn('source_id', MemberLama::where('member_gend', 'like', "%{$search}%")->pluck('member_no'));
                            });
                        });
                    }),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where(function ($q1) use ($search) {
                                $q1->where('source_type', 'table_ppab_baru')
                                    ->whereIn('source_id', MemberPpab::where('email', 'like', "%{$search}%")->pluck('id_member'));
                            })->orWhere(function ($q2) use ($search) {
                                $q2->where('source_type', 'table_member_lama')
                                    ->whereIn('source_id', MemberLama::where('member_emai', 'like', "%{$search}%")->pluck('member_no'));
                            });
                        });
                    }),
                TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where(function ($q1) use ($search) {
                                $q1->where('source_type', 'table_ppab_baru')
                                    ->whereIn('source_id', MemberPpab::where('nama_angkatan', 'like', "%{$search}%")->pluck('id_member'));
                            })->orWhere(function ($q2) use ($search) {
                                $q2->where('source_type', 'table_member_lama')
                                    ->whereIn('source_id', MemberLama::where('member_nama_angkatan', 'like', "%{$search}%")->pluck('member_no'));
                            });
                        });
                    }),
                TextColumn::make('level_angkatan')
                    ->label('Level')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('invite_portal')
                    ->label('Undang Portal')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Undangan Portal e-SII')
                    ->modalDescription(fn($record) => "Kirim email undangan login portal e-SII ke {$record->email}?")
                    ->modalSubmitActionLabel('Ya, Kirim Sekarang')
                    ->action(function ($record) {
                        if (!$record->email) {
                            Notification::make()
                                ->title('Email tidak ditemukan')
                                ->warning()
                                ->send();
                            return;
                        }
                        SendPortalInviteJob::dispatch($record);
                        Notification::make()
                            ->title('Undangan Dijadwalkan!')
                            ->body("Email undangan portal akan segera dikirim ke {$record->email}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_invite_portal')
                        ->label('Undang Portal Massal')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Undangan Portal Massal')
                        ->modalDescription('Email undangan portal e-SII akan dikirim ke semua civitas terpilih secara bertahap (jeda 5 detik per email) untuk menghindari spam.')
                        ->modalSubmitActionLabel('Ya, Kirim Semua')
                        ->action(function ($records) {
                            $delay = 0;
                            $sent = 0;
                            foreach ($records as $civitas) {
                                if (!$civitas->email) continue;
                                SendPortalInviteJob::dispatch($civitas)
                                    ->delay(now()->addSeconds($delay));
                                $delay += 5;
                                $sent++;
                            }
                            Notification::make()
                                ->title('Bulk Invite Dijadwalkan!')
                                ->body("{$sent} email undangan portal akan dikirim secara bertahap.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCivitas::route('/'),
        ];
    }
}
