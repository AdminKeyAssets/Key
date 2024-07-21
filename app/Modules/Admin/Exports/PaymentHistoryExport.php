<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\PaymentsHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentHistoryExport implements FromCollection, WithHeadings, WithColumnWidths
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
        $query = PaymentsHistory::query();

        $query->where('asset_id', '=', $this->filters['asset_id']);

        $payments = $query->select('date', 'amount')->get();

        $payments->transform(function ($payment) {
            return [
                'date' => '"' . $payment->date . '"',
                'amount' => $payment->amount,
            ];
        });


        return new Collection($payments->toArray());
    }

    public function headings(): array
    {
        return [
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
            'A' => 20,
        ];
    }
}
