<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentScheduleExport implements FromCollection, WithHeadings
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

        $payments = $query->select('number', 'payment_date', 'amount', 'left_amount')->get();

        $payments->transform(function ($payment) {
            return [
                'payment' => '"' . $payment->number . '"',
                'payment_date' => '"' . $payment->payment_date . '"',
                'amount' => $payment->amount,
                'left_amount' => $payment->left_amount,
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
            'Left Amount',
        ];
    }
}
