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

class PpabPaymentPanitiaExport implements FromCollection, WithHeadings, WithStyles, WithMapping, ShouldAutoSize
{
    protected string $sessionId;
    protected $payments;
    protected int $totalAmount = 0;
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
            'Rp ' . number_format($remain, 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->payments->count() + 1; // +1 for header

        // Header styling
        $sheet->getStyle('A1:H1')->applyFromArray([
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
            $remain = 0;
            if ($payment->payment_type === 'dp') {
                $fullPrice = $this->getFullPriceByPaket($payment->member?->paket);
                $remain = max(0, $fullPrice - $payment->amount);
            }

            // Yellow = DP (belum lunas), White = lunas
            $bgColor = ($remain > 0) ? 'FFFF00' : 'FFFFFF';

            $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor]
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);

            // Right-align amounts
            $sheet->getStyle("G{$rowNum}:H{$rowNum}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $rowNum++;
        }

        // Summary rows
        $summaryRow = $lastRow + 1;
        $sheet->setCellValue("A{$summaryRow}", 'TOTAL');
        $sheet->mergeCells("A{$summaryRow}:F{$summaryRow}");
        $sheet->setCellValue("G{$summaryRow}", 'Rp ' . number_format($this->totalAmount, 0, ',', '.'));
        $sheet->setCellValue("H{$summaryRow}", 'Rp ' . number_format($this->totalRemain, 0, ',', '.'));

        $sheet->getStyle("A{$summaryRow}:H{$summaryRow}")->applyFromArray([
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

        // Grand total
        $grandRow = $summaryRow + 1;
        $sheet->setCellValue("A{$grandRow}", 'GRAND TOTAL');
        $sheet->setCellValue("G{$grandRow}", 'Rp ' . number_format($this->grandTotal, 0, ',', '.'));
        $sheet->mergeCells("A{$grandRow}:F{$grandRow}");
        $sheet->mergeCells("G{$grandRow}:H{$grandRow}");

        $sheet->getStyle("A{$grandRow}:H{$grandRow}")->applyFromArray([
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
