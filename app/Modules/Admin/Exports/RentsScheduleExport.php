<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\Rental;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RentsScheduleExport implements FromCollection, WithHeadings
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
        $query = Rental::query();

        $query->where('asset_id', '=', $this->filters['asset_id']);

        $rents = $query->select('payment_date', 'amount', 'left_amount')->get();

        $rents->transform(function ($rent) {
            return [
                'payment_date' => '"' . $rent->payment_date . '"',
                'amount' => $rent->amount,
                'left_amount' => $rent->left_amount,
            ];
        });


        return new Collection($rents->toArray());
    }

    public function headings(): array
    {
        return [
            'Payment Date',
            'Amount',
            'Left Amount',
        ];
    }
}
