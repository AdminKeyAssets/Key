<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\Rental;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RentsPaymentsExport implements FromCollection, WithHeadings, WithColumnWidths
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
        $query = RentalPaymentsHistory::query();

        $query->where('asset_id', '=', $this->filters['asset_id']);

        $rents = $query->select('date', 'amount')->get();

        $rents->transform(function ($rent) {
            return [
                'date' => '"' . $rent->date . '"',
                'amount' => $rent->amount,
            ];
        });


        return new Collection($rents->toArray());
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
