<?php

namespace App\Exports;

use App\Models\PpabPayment;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PpabPaymentInternalExport implements FromCollection, WithHeadings, WithStyles, WithMapping, ShouldAutoSize
{
    protected string $sessionId;
    protected $payments;
    protected int $totalAmount = 0;
    protected int $totalVat = 0;
    protected int $totalSysdev = 0;
    protected int $totalWithdrawable = 0;
    protected int $totalRemain = 0;
    protected int $grandTotal = 0;
    protected ?object $sessionPrices = null;

    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function collection()
    {
        $this->payments = PpabPayment::where('id_session', $this->sessionId)
            ->where('status', 'PAID')
            ->with('member')
            ->get();

        foreach ($this->payments as $payment) {
            $this->totalAmount += $payment->amount ?? 0;
            $this->totalVat += $payment->fee_pg ?? 0;
            $this->totalSysdev += $payment->fee_sysdev ?? 0;
            $this->totalWithdrawable += $payment->withdrawable ?? 0;

            if ($payment->payment_type === 'dp') {
                $fullPrice = $this->getFullPriceByPaket($payment->member?->paket);
                $this->totalRemain += max(0, $fullPrice - $payment->amount);
            }
        }

        $this->grandTotal = $this->totalAmount + $this->totalRemain;

        return $this->payments;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Paket',
            'Tipe Bayar',
            'Channel',
            'Bank',
            'Amount',
            'VAT / Fee PG',
            'Fee Sysdev',
            'Withdrawable',
            'Sisa Pelunasan'
        ];
    }

    public function map($payment): array
    {
        static $no = 0;
        $no++;

        $remain = 0;
        if ($payment->payment_type === 'dp') {
            $fullPrice = $this->getFullPriceByPaket($payment->member?->paket);
            $remain = max(0, $fullPrice - $payment->amount);
        }

        $channel = match($payment->method) {
            'va' => 'VA',
            'qris' => 'QRIS',
            'retail' => 'Indomaret',
            default => strtoupper($payment->method ?? '-')
        };

        return [
            $no,
            $payment->member?->name ?? 'N/A',
            $payment->member?->paket ? strtoupper($payment->member->paket) : '-',
            strtoupper($payment->payment_type ?? '-'),
            $channel,
            $payment->bank_name ?? '-',
            'Rp ' . number_format($payment->amount ?? 0, 0, ',', '.'),
            'Rp ' . number_format($payment->fee_pg ?? 0, 0, ',', '.'),
            'Rp ' . number_format($payment->fee_sysdev ?? 0, 0, ',', '.'),
            'Rp ' . number_format($payment->withdrawable ?? 0, 0, ',', '.'),
            'Rp ' . number_format($remain, 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->payments->count() + 1;

        // Header styling
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Data rows
        $rowNum = 2;
        foreach ($this->payments as $payment) {
            $sheet->getStyle("A{$rowNum}:K{$rowNum}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);

            $sheet->getStyle("G{$rowNum}:K{$rowNum}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $rowNum++;
        }

        // Summary rows
        $summaryRows = [
            ['Total Amount', $this->totalAmount, 'G'],
            ['Total VAT / Fee PG', $this->totalVat, 'H'],
            ['Total Fee Sysdev', $this->totalSysdev, 'I'],
            ['Total Withdrawable', $this->totalWithdrawable, 'J'],
            ['Total Sisa Pelunasan', $this->totalRemain, 'K'],
        ];

        foreach ($summaryRows as $offset => [$label, $value, $column]) {
            $row = $rowNum + $offset;
            $sheet->setCellValue("A{$row}", $label);
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("{$column}{$row}", 'Rp ' . number_format($value, 0, ',', '.'));

            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9']
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
        }

        // Grand total
        $grandRow = $rowNum + count($summaryRows);
        $sheet->setCellValue("A{$grandRow}", 'GRAND TOTAL');
        $sheet->setCellValue("G{$grandRow}", 'Rp ' . number_format($this->grandTotal, 0, ',', '.'));
        $sheet->mergeCells("A{$grandRow}:F{$grandRow}");
        $sheet->mergeCells("G{$grandRow}:K{$grandRow}");

        $sheet->getStyle("A{$grandRow}:K{$grandRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'B7B7B7']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_MEDIUM],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        return [];
    }

    /**
     * Get full price based on member's paket (sii / bsq / sii + bsq).
     * Fallback to sii_price_full
     */
    private function getFullPriceByPaket(?string $paket): int
    {
        if (!$this->sessionPrices) {
            $this->sessionPrices = DB::connection('ppab')
                ->table('ppab_sessions')
                ->where('uuid', $this->sessionId)
                ->first([
                    'sii_price_full',
                    'sii_price_dp',
                    'bsq_price_full',
                    'bsq_price_dp'
                ]);
        }

        return match($paket) {
            'sii' => (int) ($this->sessionPrices->sii_price_full ?? 0),
            'bsq' => (int) ($this->sessionPrices->bsq_price_full ?? 0),
            'sii + bsq' => (int) (
                ($this->sessionPrices->sii_price_full ?? 0) +
                ($this->sessionPrices->bsq_price_full ?? 0)
            ),
            default => (int) ($this->sessionPrices->sii_price_full ?? 0)
        };
    }
}
