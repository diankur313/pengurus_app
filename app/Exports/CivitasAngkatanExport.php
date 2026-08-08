<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CivitasAngkatanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return ['Nama', 'Gender', 'Email', 'Angkatan'];
    }

    public function map($row): array
    {
        return [
            $row->member_name,
            ucfirst($row->member_gend ?? ''),
            $row->member_emai,
            $row->member_nama_angkatan,
        ];
    }
}
