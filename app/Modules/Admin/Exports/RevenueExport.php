<?php

namespace App\Modules\Admin\Exports;

use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\PaymentsHistory;
use App\Modules\Asset\Models\RentalPaymentsHistory;
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

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Asset::query();

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
                ->where('surname', $managerSurname)->
                first();
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



        if (!empty($this->filters['agreement_date']) && !is_null($this->filters['agreement_date']) && $this->filters['agreement_date'] !== 'null') {
            $createdDates = explode(',', $this->filters['agreement_date']);
            if (isset($createdDates[0])) {
                $query->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '>=', $createdDates[0])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        });
                });
            }
            if (isset($createdDates[1])) {
                $query->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        });
                });
            }
        }

        if (!empty($this->filters['investor']) && $this->filters['investor'] != 'all') {
            $investorNamesArray = explode(' ', $this->filters['investor']);

            $firstName = array_shift($investorNamesArray);

            $surname = implode(' ', $investorNamesArray);

            $investorUser = Investor::where('name', $firstName)
                ->where('surname', $surname)->first();

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

        $assets = $query->select('id', 'project_name', 'agreement_date', 'current_value', 'total_price')->get();

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
            'Rent',
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
