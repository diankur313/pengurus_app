<?php

namespace App\Filament\Resources\CivitasResource\Pages;

use App\Filament\Resources\CivitasResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;

class ManageCivitas extends ManageRecords
{
    protected static string $resource = CivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('synchronize')
                ->label('Synchronize Member')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Forms\Components\Select::make('level_angkatan')
                        ->label('Level Angkatan')
                        ->options([
                            'Angdas' => 'Angdas',
                            'Lanjutan' => 'Lanjutan',
                            'Pasca' => 'Pasca',
                        ])
                        ->required(),
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

                            // Search in MemberPpab
                            $ppabs = \App\Models\MemberPpab::where('name', 'like', "%{$search}%")
                                ->orWhere('nama_angkatan', 'like', "%{$search}%")
                                ->limit(30)
                                ->get();
                            foreach ($ppabs as $ppab) {
                                $results["table_ppab_baru:{$ppab->getKey()}"] = "{$ppab->name} ({$ppab->nama_angkatan}) - PPAB Baru";
                            }

                            // Search in MemberLama
                            $lamas = \App\Models\MemberLama::where('member_name', 'like', "%{$search}%")
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
                                    $member = \App\Models\MemberPpab::find($id);
                                    $labels[$value] = $member ? "{$member->name} ({$member->nama_angkatan}) - PPAB Baru" : $value;
                                } elseif ($type === 'table_member_lama') {
                                    $member = \App\Models\MemberLama::where('member_no', $id)->first();
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
                    $memberValues = $data['member'] ?? [];

                    foreach ($memberValues as $memberValue) {
                        $parts = explode(':', $memberValue);
                        if (count($parts) !== 2) continue;

                        [$type, $id] = $parts;

                        // Update or create CivitasPendidikan
                        $civitas = \App\Models\CivitasPendidikan::firstOrNew([
                            'source_type' => $type,
                            'source_id' => $id,
                        ]);

                        if (!$civitas->exists) {
                            if ($type === 'table_ppab_baru') {
                                $ppab = \App\Models\MemberPpab::find($id);
                                $civitas->uuid = ($ppab && isset($ppab->uuid)) ? $ppab->uuid : \Illuminate\Support\Str::uuid()->toString();
                            } else {
                                $civitas->uuid = \Illuminate\Support\Str::uuid()->toString();
                            }
                        }

                        $civitas->level_angkatan = $level;
                        $civitas->save();

                        // Update Master Table
                        if ($type === 'table_ppab_baru') {
                            \App\Models\MemberPpab::where('id_member', $id)->update(['level_angkatan' => $level]);
                        } elseif ($type === 'table_member_lama') {
                            \App\Models\MemberLama::where('member_no', $id)->update(['level_angkatan' => $level]);
                        }
                    }

                    // Notify
                    \Filament\Notifications\Notification::make()
                        ->title('Members synchronized successfully')
                        ->success()
                        ->send();
                })
        ];
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.components.preview-photo-modal');
    }
}
