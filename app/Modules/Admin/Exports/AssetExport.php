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

class AssetExport implements FromCollection, WithHeadings, WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $user = auth()->user();
//        dd(auth()->user());
        $userId = $user->getAuthIdentifier();

        $query = Asset::query()->orderByDesc('id');


        if (\Auth::guard('admin')->check() && auth()->user()->getRolesNameAttribute() != 'administrator') {
            $query->where('admin_id', '=', $userId);
        }

        if (\Auth::guard('investor')->check()) {
            $query = $user->assets()->orderByDesc('id');
        }

        if (\Auth::guard('developer')->check()) {
            $query->whereIn('project_name', $user->assets()->pluck('asset_name')->toArray())->where('developer_access', 1);
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
            $createdDates = explode(',', $this->filters['agreement_date']);

            if (isset($createdDates[0])) {
                $query->where('agreement_date', '>=', $createdDates[0]);
            }
            if (isset($createdDates[1])) {
                $query->where('agreement_date', '<=', $createdDates[1]);
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


        // Transform each asset by calculating the additional fields.
        $assets = $assets->map(function ($asset) {

            $investors = $asset->investors->map(function ($investor) {
                return $investor->name . ' ' . $investor->surname;
            })->implode(' / ');

            return [
                'Name' => $asset->project_name,
                'City' => $asset->city,
                'Investor' => $investors,
                'Asset Type / Size' => $asset->flat_number ? $asset->type . ' N' . $asset->flat_number . ' - ' . $asset->area . ' sq.m' : '',
                'Agreement Status' => $asset->agreement_status,
                'Next Installment' => $asset->agreement_status == 'Installments'
                && count($asset->payments)
                && count($asset->payments->where('status', 0))
                    ? (
                    strtotime($asset->payments->where('status', 0)->first()->payment_date) < time()
                        ?
                        // Overdue: display formatted overdue date & sum of overdue left_amount
                        \Carbon\Carbon::parse($asset->payments->where('status', 0)->first()->payment_date)->format('Y/m/d')
                        . ' - ' . number_format(
                            $asset->payments->where('status', 0)
                                ->filter(function ($payment) {
                                    return strtotime($payment->payment_date) < time();
                                })->sum('left_amount'), 0, ".", ",") . '$'
                        :
                        // Not overdue: display the first record's payment_date and left_amount
                        $asset->payments->where('status', 0)->first()->payment_date
                        . ' - ' . number_format($asset->payments->where('status', 0)->first()->left_amount, 0, ".", ",") . '$'
                    )
                    : '',
                'Next Rent' => $asset->asset_status == 'Rented'
                && count($asset->rentals)
                && count($asset->rentals->where('status', 0))
                    ? (
                    strtotime($asset->rentals->where('status', 0)->first()->payment_date) < time()
                        ?
                        // Overdue: display formatted overdue date & sum of overdue left_amount
                        \Carbon\Carbon::parse($asset->rentals->where('status', 0)->first()->payment_date)->format('Y/m/d')
                        . ' - ' . number_format(
                            $asset->rentals->where('status', 0)
                                ->filter(function ($rental) {
                                    return strtotime($rental->payment_date) < time();
                                })->sum('left_amount'), 0, ".", ",") . '$'
                        :
                        // Not overdue: display the first record's payment_date and left_amount
                        $asset->rentals->where('status', 0)->first()->payment_date
                        . ' - ' . number_format($asset->rentals->where('status', 0)->first()->left_amount, 0, ".", ",") . '$'
                    )
                    : ''
            ];
        });


        return new Collection($assets->toArray());
    }

    public function headings(): array
    {
        return [
            'Name',
            'City',
            'Investor',
            'Asset Type / Size',
            'Agreement Status',
            'Next Installment',
            'Next Rent',
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
