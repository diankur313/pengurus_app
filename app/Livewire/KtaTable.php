<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class KtaTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * Query UNION antara Member Lama (yisc_db_lama.member)
     * dan Member PPAB (ppab.ppab_member).
     *
     * Karena kedua DB berada di server MySQL yang sama, kita gunakan
     * cross-schema reference melalui satu DB connection (yisic_db_lama).
     */
    protected function getUnionQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // DB Lama
        $q1 = DB::connection('yisic_db_lama')
            ->table('member')
            ->select([
                DB::raw('member_no AS id'),
                DB::raw('member_name AS name'),
                DB::raw('member_gend AS gender'),
                DB::raw('member_dob AS dob'),
                DB::raw('member_nama_angkatan AS angkatan'),
                DB::raw('member_pict AS photo'),
                DB::raw("'lama' AS source"),
            ]);

        // DB PPAB (cross-schema reference)
        $q2 = DB::connection('yisic_db_lama')
            ->table('ppab.ppab_member')
            ->select([
                DB::raw('id_member AS id'),
                'name',
                'gender',
                DB::raw('tgl_lahir AS dob'),
                DB::raw('nama_angkatan AS angkatan'),
                'photo',
                DB::raw("'ppab' AS source"),
            ]);

        $union = $q1->unionAll($q2);

        return \App\Models\MemberLama::query()
            ->fromSub($union, 'u');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getUnionQuery())
            ->columns([
                // Foto Profil
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->size(80)
                    ->getStateUsing(fn ($record) =>
                        filled($record->photo) && $record->photo !== 'avatar.png'
                            ? url('/profile-picture/' . $record->photo)
                            : null
                    )
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'U') . '&color=FFFFFF&background=09090b'
                    )
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "\$dispatch('open-preview', { url: '" . (
                            filled($record->photo) && $record->photo !== 'avatar.png'
                                ? url('/profile-picture/' . $record->photo)
                                : 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'U') . '&color=FFFFFF&background=09090b'
                        ) . "' }); \$dispatch('open-modal', { id: 'preview-photo-modal' });",
                        'style' => 'cursor: pointer;',
                    ]),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '-'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dob')
                    ->label('TTL')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('angkatan')
                    ->label('Angkatan')
                    ->searchable()
                    ->sortable(),

                // Kolom Kartu Anggota — thumbnail statis kta_raw.png, klik = download
                ImageColumn::make('member_card')
                    ->label('Kartu Anggota')
                    ->getStateUsing(fn () => asset('kta_raw.png'))
                    ->width(120)
                    ->height(75)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click' => "window.open('" . url('/kta-download/' . $record->source . '/' . $record->id) . "', '_blank');",
                        'style'      => 'cursor: pointer; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: transform 0.15s; display: block;',
                        'title'      => 'Klik untuk download KTA',
                        'x-on:mouseenter' => "\$el.style.transform='scale(1.05)'",
                        'x-on:mouseleave' => "\$el.style.transform='scale(1)'",
                    ]),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public function render()
    {
        return view('livewire.kta-table');
    }
}
