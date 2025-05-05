<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RevenueExport implements FromCollection, WithHeadings, WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();

        $query = Asset::query()->orderByDesc('id');

        if (auth()->user()->getRolesNameAttribute() != 'administrator') {
            $query->where('admin_id', '=', $userId);
        }
        // Apply filters as in your index function.
        if (!empty($this->filters['asset']) && $this->filters['asset'] != 'all') {
            $query->where('project_name', 'like', '%' . $this->filters['asset'] . '%');
        }

        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('sale_status', $this->filters['status']);
        }

        if (!empty($this->filters['manager']) && $this->filters['manager'] != 'all') {
            $managerNamesArray = explode(' ', $this->filters['manager']);
            $managerFirstName = array_shift($managerNamesArray);
            $managerSurname = implode(' ', $managerNamesArray);
            $managerUser = Admin::where('name', $managerFirstName)
                ->where('surname', $managerSurname)
                ->first();
            if (isset($managerUser->id)) {
                $query->where('admin_id', $managerUser->id);
            }
        }

        if (!empty($this->filters['asset_type']) && $this->filters['asset_type'] != 'all') {
            $query->where('type', $this->filters['asset_type']);
        }

        if (!empty($this->filters['asset_status']) && $this->filters['asset_status'] != 'all') {
            $query->where('asset_status', $this->filters['asset_status']);
        }

        if (!empty($this->filters['agreement_status']) && $this->filters['agreement_status'] != 'all') {
            $query->where('agreement_status', $this->filters['agreement_status']);
        }

        if (!empty($this->filters['agreement_date']) && $this->filters['agreement_date'] !== 'null') {

            if(!is_array($this->filters['agreement_date'])){
                $createdDates = explode(',', $this->filters['agreement_date']);
            }else{
                $createdDates = $this->filters['agreement_date'];
            }
            if (isset($createdDates[0])) {
                $query->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '>=', $createdDates[0])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('investments', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        });
                });
            }
            if (isset($createdDates[1])) {
                $query->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('investments', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        });
                });
            }
        }

        if (!empty($this->filters['investor']) && $this->filters['investor'] != 'all') {
            $investorNamesArray = explode(' ', $this->filters['investor']);
            $firstName = array_shift($investorNamesArray);
            $surname = implode(' ', $investorNamesArray);
            $investorUser = Investor::where('name', $firstName)
                ->where('surname', $surname)
                ->first();
            if (isset($investorUser->id)) {
                $query->whereHas('investors', function ($query) use ($investorUser) {
                    $query->where('id', $investorUser->id);
                });
            }
        }

        if (\Auth::guard('investor')->check()) {
            $user = auth('investor')->user();
            $query->whereHas('investors', function ($q) use ($user) {
                $q->where('id', $user->getAuthIdentifier());
            });
        }

        // Eager load relationships for calculations.
        $query->with(['investors', 'rentalPaymentsHistories', 'paymentsHistories', 'investments']);
        $assets = $query->get();

        // Set up date filters if provided.
        $startDate = null;
        $endDate = null;
        if (!empty($this->filters['agreement_date']) && $this->filters['agreement_date'] !== 'null') {
            if(!is_array($this->filters['agreement_date'])){
                $createdDates = explode(',', $this->filters['agreement_date']);
            }else{
                $createdDates = $this->filters['agreement_date'];
            }
            if (isset($createdDates[0])) {
                $startDate = $createdDates[0];
            }
            if (isset($createdDates[1])) {
                $endDate = $createdDates[1];
            }
        }

        // Transform each asset by calculating the additional fields.
        $assets = $assets->map(function ($asset) use ($startDate, $endDate) {
            // Retrieve collections for the calculations.
            $rent = $asset->rentalPaymentsHistories;
            $paid = $asset->paymentsHistories;
            $allInvestments = $asset->investments;
            $renovationInvestmentCollection = $asset->investments->where('status', 'Renovation');
            // Assume renovationPaymentsHistories() returns a collection.
            $renovationPaymentsCollection = $asset->renovationPaymentsHistories();

            // Apply date filters if set.
            if ($startDate) {
                $rent = $rent->where('date', '>=', $startDate);
                $paid = $paid->where('date', '>=', $startDate);
                $allInvestments = $allInvestments->where('date', '>=', $startDate);
                $renovationInvestmentCollection = $renovationInvestmentCollection->where('date', '>=', $startDate);
                $renovationPaymentsCollection = $renovationPaymentsCollection->where('date', '>=', $startDate);
            }
            if ($endDate) {
                $rent = $rent->where('date', '<=', $endDate);
                $paid = $paid->where('date', '<=', $endDate);
                $allInvestments = $allInvestments->where('date', '<=', $endDate);
                $renovationInvestmentCollection = $renovationInvestmentCollection->where('date', '<=', $endDate);
                $renovationPaymentsCollection = $renovationPaymentsCollection->where('date', '<=', $endDate);
            }
            if ($asset->sale_status === 'sold' && $asset->sale_date) {
                $rent = $rent->where('date', '<=', $asset->sale_date);
            }

            // Sum up the amounts.
            $rentSum = $rent->sum('amount');
            $paidSum = $paid->sum('amount');
            $allInvestmentsSum = $allInvestments->sum('amount');
            $renovationInvestmentSum = $renovationInvestmentCollection->sum('amount');
            $renovationPaymentsSum = $renovationPaymentsCollection->sum('amount');

            $otherInvestments = $allInvestmentsSum - $renovationInvestmentSum;
            $renovationTotal = $renovationInvestmentSum + $renovationPaymentsSum;

            // Calculate Total Investment and Paid based on agreement_status.
            if ($asset->agreement_status === 'Installments') {
                $totalInvestment = $paidSum + $allInvestmentsSum + $renovationPaymentsSum;
                $computedPaid = $paidSum;
            } else {
                $totalInvestment = $asset->total_price + $allInvestmentsSum + $renovationPaymentsSum;
                $computedPaid = $asset->total_price;
            }

            // Compute percentage: if total_price is zero, default to 0%.
            $percentage = $asset->total_price ? ($computedPaid / $asset->total_price) * 100 : 0;
            // Format the percentage like in the view.
            $percentage = (fmod($percentage, 1) == 0) ? number_format($percentage, 0) : number_format($percentage, 2);

            // Determine Capital Gain, Net Cash Balance, and what to display as Current Value.
            if ($asset->sale_status !== 'sold') {
                $capitalGain = $asset->current_value - ($asset->total_price + $renovationTotal);
                $netCashBalance = $rentSum - $otherInvestments;
                $currentValueDisplay = $asset->current_value;
            } else {
                $capitalGain = $asset->sale_price - $totalInvestment;
                $netCashBalance = $asset->sale_price + $rentSum - $totalInvestment;
                $currentValueDisplay = $asset->sale_price;
            }

            // Concatenate investor names.
            $investors = $asset->investors->map(function ($investor) {
                return $investor->name . ' ' . $investor->surname;
            })->implode(' / ');

            return [
                'Name'              => $asset->project_name,
                'Investor'          => $investors,
                'Purchase Date'     => $asset->agreement_date,
                'Purchase Price'    => $asset->total_price,
                'Paid'              => number_format($computedPaid, 0, ".", ",") . '$ - ' . $percentage . '%',
                'Renovation'        => $renovationTotal,
                'Other Investment'  => $otherInvestments,
                'Total Investment'  => $totalInvestment,
                'Current Value'     => $currentValueDisplay,
                'Capital Gain'      => $capitalGain,
                'Rent'              => $rentSum,
                'Net Cash Balance'  => $netCashBalance,
            ];
        });


        return new Collection($assets->toArray());
    }

    public function headings(): array
    {
        return [
            'Name',
            'Investor',
            'Purchase Date',
            'Purchase Price',
            'Paid',
            'Renovation',
            'Other Investment',
            'Total Investment',
            'Current Value',
            'Capital Gain',
            'Rent',
            'Net Cash Balance',
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
}
