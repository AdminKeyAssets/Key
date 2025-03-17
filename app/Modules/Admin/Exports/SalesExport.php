<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
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

        if (auth()->user()->getRolesNameAttribute() != 'administrator') {
            $query->where('admin_id', auth()->user()->getAuthIdentifier());
        }

        if (!empty($this->filters['agreement_date'])) {
            $agreementDates = explode(',', $this->filters['agreement_date']);

            if (isset($agreementDates[0])) {
                $query->where('agreement_date', '>=', $agreementDates[0]);
            }
            if (isset($agreementDates[1])) {
                $query->where('agreement_date', '<=', $agreementDates[1]);
            }
        }

        if (!empty($this->filters['manager']) && $this->filters['manager'] != 'all') {
            $managerNamesArray = explode(' ', $this->filters['manager']);
            $managerUser = Admin::where('name', $managerNamesArray[0])
                ->where('surname', $managerNamesArray[1])->first();
            $query->where('admin_id', '=', $managerUser->id);
        }

        if (!empty($this->filters['marketing_channel']) && $this->filters['marketing_channel'] != 'all') {
            $query->where('marketing_channel', '=', $this->filters['marketing_channel']);
        }

        if (!empty($this->filters['status']) && $this->filters['status'] == 'Complete') {
            $query->where('complete', '=', 1);
        }

        if (!empty($this->filters['status']) && $this->filters['status'] == 'Pending') {
            $query->where('complete', '!=', 1);
        }

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
