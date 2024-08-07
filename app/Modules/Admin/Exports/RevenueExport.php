<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\PaymentsHistory;
use App\Modules\Asset\Models\Rental;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueExport implements FromCollection, WithHeadings, WithColumnWidths
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
        $query = Asset::query();
        if(\Auth::guard('investor')->check()){
            $user = auth('investor')->user();
            $query->where('investor_id', $user->getAuthIdentifier());
        }
        $assets = $query->select('id','project_name', 'agreement_date', 'current_value', 'total_price')->get();

        $assets->transform(function ($asset) {
            $paymentsHistories = PaymentsHistory::where('asset_id', $asset->id)->sum('amount');
            $rentalPaymentsHistories = RentalPaymentsHistory::where('asset_id', $asset->id)->sum('amount');

            return [
                'project_name' => $asset->project_name,
                'purchase_date' => '"' . $asset->agreement_date . '"',
                'purchase_price' => $asset->total_price,
                'investment' => $paymentsHistories,
                'current_value' => $asset->current_value,
                'capital_gain' => $asset->current_value - $asset->total_price,
                'rent' => $rentalPaymentsHistories,

            ];
        });


        return new Collection($assets->toArray());
    }

    public function headings(): array
    {
        return [
            'Project Name',
            'Purchase Date',
            'Purchase Price',
            'Total Investment',
            'Current Value',
            'Capital Gain',
            'Rent'
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
