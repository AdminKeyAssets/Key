<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Asset\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PaymentScheduleExport implements FromCollection, WithHeadings, WithEvents
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
                'number' => '"' . $payment->number . '"',
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

    /**
     * Automatically adjust column width after the sheet is generated.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set auto-size for all columns
                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            },
        ];
    }
}
