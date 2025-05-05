<?php

namespace App\Modules\Admin\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class AgreementDetailsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $payments;
    protected $paymentsHistories;

    public function __construct($data, $payments, $paymentsHistories)
    {
        $this->data = $data;
        $this->payments = $payments;
        $this->paymentsHistories = $paymentsHistories;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection([$this->data]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Investor',
            'Investor ID',
            'Email',
            'Asset Name',
            'Asset Type',
            'Size (m²)',
            'Unit Number',
            'Sqm Price',
            'Total Price',
            'Installment Period'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['investorNames'],
            $row['investorId'],
            $row['investorEmail'],
            $row['assetName'] ?? 'N/A',
            $row['assetType'],
            $row['area'],
            $row['flatNumber'],
            $this->formatPrice($row['price']),
            $this->formatPrice($row['totalPrice']),
            $row['period'] . ' Month(s)',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Agreement Details';
    }

    /**
     * Format price with commas for thousands
     */
    private function formatPrice($amount)
    {
        if ($amount !== undefined && $amount !== '') {
            if (is_numeric($amount)) {
                if ($amount % 1 === 0) {
                    // No decimal places for whole numbers
                    return number_format($amount, 0) . '$';
                } else {
                    return number_format($amount, 2) . '$';
                }
            }
        }
        return '0.00$';
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
