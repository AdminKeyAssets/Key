<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentScheduleExport implements FromCollection, WithHeadings, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Payment::query();

        $query->where('asset_id', '=', $this->filters['asset_id']);

        $payments = $query->select('number', 'payment_date', 'amount')->get();

        $payments->transform(function ($payment) {
            return [
                'payment' => '"' . $payment->number . '"',
                'payment_date' => '"' . $payment->payment_date . '"',
                'amount' => $payment->amount,
            ];
        });


        return new Collection($payments->toArray());
    }

    public function headings(): array
    {
        return [
            'Number',
            'Payment Date',
            'Amount',
        ];
    }

    public function setColumnAutoSize(Worksheet $sheet)
    {
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
    }

    public function columnWidths(): array
    {
        return [
            'B' => 20,
        ];
    }
}
