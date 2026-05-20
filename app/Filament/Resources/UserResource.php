<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users Management';
    protected static ?string $navigationGroup = 'Filament Shield';
    protected static ?string $modelLabel = 'Users Management';
    protected static ?string $pluralModelLabel = 'Users Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi User')
                    ->schema([
                        Forms\Components\Select::make('email')
                            ->label('Cari Nama')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                if (empty($search)) {
                                    return [];
                                }

                                $localEmails = \App\Models\User::pluck('email')->toArray();

                                // Ambil hanya dari database eksternal berdasarkan NAMA
                                $queryLama = \Illuminate\Support\Facades\DB::connection('yisic_db_lama')
                                    ->table('member')
                                    ->where('member_name', 'like', "%{$search}%")
                                    ->whereNotIn('member_emai', $localEmails)
                                    ->limit(10)
                                    ->pluck('member_name', 'member_emai') // [email => nama]
                                    ->toArray();

                                $queryPPAB = \Illuminate\Support\Facades\DB::connection('ppab')
                                    ->table('ppab_member')
                                    ->where('name', 'like', "%{$search}%")
                                    ->whereNotIn('email', $localEmails)
                                    ->limit(10)
                                    ->pluck('name', 'email') // [email => nama]
                                    ->toArray();

                                return array_merge($queryLama, $queryPPAB);
                            })
                            ->getOptionLabelUsing(function ($value) {
                                // Mencari label nama berdasarkan email yang dipilih
                                $local = \App\Models\User::where('email', $value)->first();
                                if ($local) return $local->name;

                                try {
                                    $lama = \Illuminate\Support\Facades\DB::connection('yisic_db_lama')->table('member')->where('member_emai', $value)->first();
                                    if ($lama) return $lama->member_name;
                                } catch (\Exception $e) {}

                                try {
                                    $ppab = \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_member')->where('email', $value)->first();
                                    if ($ppab) return $ppab->name;
                                } catch (\Exception $e) {}

                                return $value;
                            })
                            ->options(function() {
                                $localEmails = \App\Models\User::pluck('email')->toArray();

                                $lama = [];
                                try {
                                    $lama = \Illuminate\Support\Facades\DB::connection('yisic_db_lama')
                                        ->table('member')
                                        ->whereNotIn('member_emai', $localEmails)
                                        ->orderBy('member_name')
                                        ->limit(10)
                                        ->pluck('member_name', 'member_emai')
                                        ->toArray();
                                } catch (\Exception $e) {}

                                $ppab = [];
                                try {
                                    $ppab = \Illuminate\Support\Facades\DB::connection('ppab')
                                        ->table('ppab_member')
                                        ->whereNotIn('email', $localEmails)
                                        ->orderBy('name')
                                        ->limit(10)
                                        ->pluck('name', 'email')
                                        ->toArray();
                                } catch (\Exception $e) {}

                                return array_merge($lama, $ppab);
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) {
                                    $set('name', null);
                                    $set('email_display', null);
                                    $set('whatsapp', null);
                                    $set('angkatan', null);
                                    $set('gender', null);
                                    return;
                                }

                                // Set field Email Display
                                $set('email_display', $state);
                                
                                // Cari di Database yisic_db_lama
                                try {
                                    $userLama = \Illuminate\Support\Facades\DB::connection('yisic_db_lama')
                                        ->table('member')
                                        ->where('member_emai', $state)
                                        ->first();

                                    if ($userLama) {
                                        $set('name', $userLama->member_name ?? null);
                                        $set('whatsapp', $userLama->member_hp ?? null);
                                        $set('angkatan', $userLama->member_nama_angkatan ?? null);
                                        
                                        // Normalisasi Gender
                                        $rawGender = strtolower($userLama->member_gend ?? '');
                                        if (in_array($rawGender, ['l', 'laki-laki', 'pria', 'male'])) {
                                            $set('gender', 'pria');
                                        } elseif (in_array($rawGender, ['p', 'perempuan', 'wanita', 'female'])) {
                                            $set('gender', 'wanita');
                                        } else {
                                            $set('gender', null);
                                            \Filament\Notifications\Notification::make()
                                                ->title('User belum memiliki data gender')
                                                ->warning()
                                                ->send();
                                        }
                                        return;
                                    }
                                } catch (\Exception $e) {}

                                // Cari di Database PPAB
                                try {
                                    $userPPAB = \Illuminate\Support\Facades\DB::connection('ppab')
                                        ->table('ppab_member')
                                        ->where('email', $state)
                                        ->first();

                                    if ($userPPAB) {
                                        $set('name', $userPPAB->name ?? null);
                                        $set('whatsapp', $userPPAB->whatsapp ?? null);
                                        $set('angkatan', $userPPAB->nama_angkatan ?? null);

                                        // Normalisasi Gender
                                        $rawGender = strtolower($userPPAB->gender ?? '');
                                        if (in_array($rawGender, ['l', 'laki-laki', 'pria', 'male'])) {
                                            $set('gender', 'pria');
                                        } elseif (in_array($rawGender, ['p', 'perempuan', 'wanita', 'female'])) {
                                            $set('gender', 'wanita');
                                        } else {
                                            $set('gender', null);
                                            \Filament\Notifications\Notification::make()
                                                ->title('User belum memiliki data gender')
                                                ->warning()
                                                ->send();
                                        }
                                        return;
                                    }
                                } catch (\Exception $e) {}
                            }),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->readOnly()
                            ->extraAttributes(['tabindex' => '-1', 'style' => 'pointer-events: none;']),
                        Forms\Components\TextInput::make('email_display')
                            ->label('Email')
                            ->readOnly()
                            ->extraAttributes(['tabindex' => '-1', 'style' => 'pointer-events: none;']),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->readOnly()
                            ->extraAttributes(['tabindex' => '-1', 'style' => 'pointer-events: none;']),
                        Forms\Components\TextInput::make('angkatan')
                            ->label('Angkatan')
                            ->readOnly()
                            ->extraAttributes(['tabindex' => '-1', 'style' => 'pointer-events: none;']),
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->placeholder('-- Please Chose --')
                            ->options([
                                'pria' => 'Pria',
                                'wanita' => 'Wanita',
                            ])
                            ->required()
                            ->disabled(fn ($get) => filled($get('gender')))
                            ->dehydrated(),
                    ])->columns(2),

                Forms\Components\Section::make('Hak Akses')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role / Peran')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
