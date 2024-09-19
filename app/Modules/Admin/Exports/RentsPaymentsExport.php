<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Asset\Models\RentalPaymentsHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RentsPaymentsExport implements FromCollection, WithHeadings, WithEvents
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
