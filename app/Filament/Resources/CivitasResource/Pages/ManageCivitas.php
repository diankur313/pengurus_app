<?php

namespace App\Filament\Resources\CivitasResource\Pages;

use App\Filament\Resources\CivitasResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class ManageCivitas extends ManageRecords
{
    protected static string $resource = CivitasResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\CivitasResource\Widgets\CivitasStatsWidget::class,
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        $paketFilter = request()->query('paketFilter');
        
        if ($paketFilter) {
            switch ($paketFilter) {
                case 'sii':
                    $query->where('paket', 'like', '%sii%')
                        ->where('paket', 'not like', '%bsq%');
                    break;
                    
                case 'bsq':
                    $query->where('paket', 'like', '%bsq%')
                        ->where('paket', 'not like', '%sii%');
                    break;
                    
                case 'sii_bsq':
                    $query->where('paket', 'like', '%sii%')
                        ->where('paket', 'like', '%bsq%');
                    break;
            }
        }
        
        return $query;
    }

    public function getTitle(): string
    {
        $paketFilter = request()->query('paketFilter');
        
        if ($paketFilter) {
            $filterName = match($paketFilter) {
                'sii' => 'SII',
                'bsq' => 'BSQ',
                'sii_bsq' => 'SII + BSQ',
                default => ''
            };
            
            return "Daftar Civitas - Paket {$filterName}";
        }
        
        return 'Daftar Civitas';
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\Action::make('synchronize')
                ->label('Add Member')
                ->icon('heroicon-o-user-plus')
                ->form([
                    Forms\Components\Select::make('level_angkatan')
                        ->label('Semester')
                        ->options([
                            'semester_1' => 'Semester 1',
                            'semester_2' => 'Semester 2',
                            'semester_3' => 'Semester 3',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('paket')
                        ->label('Paket')
                        ->options([
                            'sii' => 'SII',
                            'bsq' => 'BSQ',
                            'sii + bsq' => 'SII + BSQ',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('member')
                        ->label('Member')
                        ->multiple()
                        ->searchable()
                        ->searchDebounce(500)
                        ->extraAttributes(['style' => 'margin-bottom: 180px;'])
                        ->getSearchResultsUsing(function (string $search): array {
                            if (strlen($search) < 3) {
                                return [];
                            }

                            $results = [];

                            // Search in MemberPpab (by name or email)
                            $ppabs = \App\Models\MemberPpab::whereNotNull('id_member')
                                ->where('id_member', '!=', '')
                                ->where(function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->orWhere('nama_angkatan', 'like', "%{$search}%");
                                })
                                ->limit(30)
                                ->get();
                            foreach ($ppabs as $ppab) {
                                // Use the unique auto-increment id, not id_member (which has duplicates)
                                $results["table_ppab_baru:{$ppab->id}"] = "{$ppab->name} ({$ppab->nama_angkatan}) - PPAB Baru";
                            }

                            // Search in MemberLama (by name or email)
                            $lamas = \App\Models\MemberLama::where('member_name', 'like', "%{$search}%")
                                ->orWhere('member_emai', 'like', "%{$search}%")
                                ->orWhere('member_nama_angkatan', 'like', "%{$search}%")
                                ->limit(30)
                                ->get();
                            foreach ($lamas as $lama) {
                                $results["table_member_lama:{$lama->getKey()}"] = "{$lama->member_name} ({$lama->member_nama_angkatan}) - Member Lama";
                            }

                            return $results;
                        })
                        ->getOptionLabelsUsing(function (array $values): array {
                            $labels = [];
                            foreach ($values as $value) {
                                if (!$value) continue;

                                $parts = explode(':', $value);
                                if (count($parts) !== 2) {
                                    $labels[$value] = $value;
                                    continue;
                                }

                                [$type, $id] = $parts;

                                if ($type === 'table_ppab_baru') {
                                    $member = \App\Models\MemberPpab::where('id', $id)->first();
                                    $labels[$value] = $member ? "{$member->name} ({$member->nama_angkatan}) - PPAB Baru" : $value;
                                } elseif ($type === 'table_member_lama') {
                                    $member = \App\Models\MemberLama::find($id);
                                    $labels[$value] = $member ? "{$member->member_name} ({$member->member_nama_angkatan}) - Member Lama" : $value;
                                } else {
                                    $labels[$value] = $value;
                                }
                            }
                            return $labels;
                        })
                        ->required(),
                ])
                ->action(function (array $data) {
                    $level = strtolower($data['level_angkatan']);
                    $paket = $data['paket'];
                    $memberValues = $data['member'] ?? [];

                    foreach ($memberValues as $memberValue) {
                        $parts = explode(':', $memberValue);
                        if (count($parts) !== 2) continue;

                        [$type, $id] = $parts;

                        // Guard against empty IDs (e.g., ppab_member with blank id_member)
                        if ($id === '' || $id === null) continue;

                        // Update or create CivitasPendidikan
                        $civitas = \App\Models\CivitasPendidikan::firstOrNew([
                            'source_type' => $type,
                            'source_id' => $id,
                        ]);

                        if ($type === 'table_ppab_baru') {
                            $ppab = \App\Models\MemberPpab::where('id', $id)->first();
                            if (!$civitas->exists) {
                                $civitas->uuid = ($ppab && isset($ppab->uuid)) ? $ppab->uuid : \Illuminate\Support\Str::uuid()->toString();
                            }
                        } else {
                            if (!$civitas->exists) {
                                $civitas->uuid = \Illuminate\Support\Str::uuid()->toString();
                            }
                        }

                        // Set paket and level_angkatan from form selection
                        $civitas->paket = $paket;
                        $civitas->level_angkatan = $level;

                        $civitas->save();

                        // Update Master Table
                        if ($type === 'table_ppab_baru') {
                            // ppab_member.level_angkatan is an ENUM of the same string
                            // values as civitas ('semester_1' dst) — no conversion needed.
                            // (Previously this cast to int, which MySQL silently reinterpreted
                            // as an ENUM index, corrupting the value — e.g. 1 became 'dasar'.)
                            \App\Models\MemberPpab::where('id', $id)->update([
                                'level_angkatan' => $level,
                                'paket' => $paket,
                            ]);
                        } elseif ($type === 'table_member_lama') {
                            $ml = \App\Models\MemberLama::find($id);
                            if ($ml) {
                                $ml->update(['level_angkatan' => $level]);
                            }
                        }
                    }

                    // Notify
                    \Filament\Notifications\Notification::make()
                        ->title('Members synchronized successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('syncLevelFromPpab')
                ->label('Sync Level & Paket PPAB')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Level & Paket')
                ->modalDescription('Sinkronkan level_angkatan & paket di civitas dari ppab_member (source: table_ppab_baru). Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Sinkronkan')
                ->action(function () {
                    $updated = 0;
                    $skipped = 0;

                    $civitasRecords = \App\Models\CivitasPendidikan::where('source_type', 'table_ppab_baru')
                        ->whereNotNull('source_id')
                        ->get();

                    foreach ($civitasRecords as $civitas) {
                        $member = \App\Models\MemberPpab::where('id', $civitas->source_id)->first();

                        if (!$member) {
                            $skipped++;
                            continue;
                        }

                        // ppab_member.level_angkatan adalah ENUM string yang sama persis
                        // dengan format civitas ('semester_1' dst, atau legacy 'dasar'/
                        // 'lanjutan'/'pasca') — tidak perlu konversi numerik.
                        $mappedLevel = !empty($member->level_angkatan) ? $member->level_angkatan : null;

                        $updates = [];

                        // Jangan pernah menimpa civitas.paket dengan NULL — civitas adalah
                        // acuan (source of truth), ppab_member yang kosong tidak boleh menang.
                        if (!empty($member->paket) && $civitas->getRawOriginal('paket') !== $member->paket) {
                            $updates['paket'] = $member->paket;
                        }

                        if ($mappedLevel !== null && $civitas->getRawOriginal('level_angkatan') !== $mappedLevel) {
                            $updates['level_angkatan'] = $mappedLevel;
                        }

                        if (!empty($updates)) {
                            $civitas->update($updates);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Sinkronisasi selesai')
                        ->body("Updated: {$updated} record(s) • Skipped: {$skipped} record(s)")
                        ->success()
                        ->send();
                }),
        ];

        $paketFilter = request()->query('paketFilter');
        if ($paketFilter) {
            $actions[] = Actions\Action::make('clearFilter')
                ->label('Clear Filter')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->url(route('filament.admin.resources.civitas.index'))
                ->outlined();
        }

        return $actions;
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.components.preview-photo-modal');
    }
}
