<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Lead\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SalesExport implements FromCollection, WithHeadings, WithEvents
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
        $query = Sale::query();

        $sales = $query->select(
            'project',
            'investor',
            'type',
            'size',
            'price',
            'total_price',
            'agreement_status',
            'agreement_date',
            'down_payment',
            'period',
            'marketing_channel',
            'commission'
        )->get();

        $sales->transform(function ($sale) {

            return [
                'project' => $sale->project,
                'investor' => $sale->investor,
                'type' => $sale->type,
                'size' => $sale->size,
                'price' => $sale->price,
                'total_price' => $sale->total_price,
                'agreement_status' => $sale->agreement_status,
                'agreement_date' => '"' . $sale->agreement_date . '"',
                'down_payment' => $sale->down_payment,
                'period' => $sale->period,
                'marketing_channel' => $sale->marketing_channel,
                'commission' => $sale->commission,
            ];
        });

        return new Collection($sales->toArray());
    }

    public function headings(): array
    {
        return [
            'Project',
            'Investor',
            'Type',
            'Size',
            'Price',
            'Total Price',
            'Agreement Status',
            'Agreement Date',
            'Down Payment',
            'Period',
            'Marketing Channel',
            'Commission',
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

                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            },
        ];
    }
}
