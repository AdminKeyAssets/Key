<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Investment;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use App\Modules\Asset\Models\Tenant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RevenueRentalExport implements FromCollection, WithHeadings, WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $assetId = $this->filters['asset_id'];
        $tenants = Tenant::where('asset_id', $assetId)->orderByDesc('id')->get();

        $tenants = $tenants->map(function ($tenant) use ($assetId) {
            $tenantRentalPaymentsAmount = RentalPaymentsHistory::where('asset_id', $assetId)
            ->where('tenant_id', $tenant->id)->sum('amount');

            $period = $tenant->agreement_term;

            $investments = Investment::where('asset_id', $assetId)
                ->where('status', '!=', 'Renovation')
                ->where('date', '>=', $tenant->agreement_date);

            if ($tenant->rent_end_date) {
                $period = $this->fractionalMonthDifference($tenant->agreement_date, $tenant->rent_end_date);
                $investments = $investments->where('date', '<=', $tenant->rent_end_date);
            }
            $investmentsAmount = $investments->sum('amount');

            return [
                'tenant_name' => $tenant->full_name ?? ($tenant->name . ' ' . $tenant->surname),
                'rent_date' => $tenant->agreement_date,
                'rent_period' => $period,
                'total_rent_payments' => $tenantRentalPaymentsAmount,
                'total_investments' => $investmentsAmount,
                'net_cash_balance' => $tenantRentalPaymentsAmount - $investmentsAmount,
            ];
        });


        return new Collection($tenants->toArray());
    }

    public function headings(): array
    {
        return [
            'Tenant Name',
            'Rent Date',
            'Rent Period',
            'Total Rent Payments',
            'Total Investment',
            'Net Cash Balance'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Auto-size all columns.
                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            },
        ];
    }

    public function fractionalMonthDifference($date1, $date2)
    {
        $d1 = \DateTime::createFromFormat('Y/m/d', $date1);
        $d2 = \DateTime::createFromFormat('Y/m/d', $date2);

        if ($d1 > $d2) {
            list($d1, $d2) = [$d2, $d1];
        }

        $fullMonths = ($d2->format('Y') - $d1->format('Y')) * 12 + ($d2->format('n') - $d1->format('n'));

        $startDay = (int)$d1->format('j');
        $endDay = (int)$d2->format('j');
        $daysInStartMonth = (int)$d1->format('t');

        if ($endDay < $startDay) {
            $fraction = $endDay / $startDay;
            $result = $fullMonths - 1 + $fraction;
        } else {
            $fraction = ($endDay - $startDay) / $daysInStartMonth;
            $result = $fullMonths + $fraction;
        }

        return number_format($result, 1);
    }
}
