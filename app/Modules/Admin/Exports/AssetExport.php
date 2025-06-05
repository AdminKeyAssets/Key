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
use Carbon\Carbon;

class AssetExport implements FromCollection, WithHeadings, WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $user      = auth()->user();
        $userId    = $user->getAuthIdentifier();
        $query     = Asset::query()->orderByDesc('id');
        $isDeveloper = false;

        if (\Auth::guard('admin')->check() && auth()->user()->getRolesNameAttribute() !== 'administrator') {
            $query->where('admin_id', $userId);
        }

        if (\Auth::guard('investor')->check()) {
            $query = $user->assets()->orderByDesc('id');
        }

        if (\Auth::guard('developer')->check()) {
            $query->whereIn('project_name', $user->assets()->pluck('asset_name')->toArray())
                ->where('developer_access', 1);
            $isDeveloper = true;
        }

        // Apply asset‐level filters
        if (!empty($this->filters['asset']) && $this->filters['asset'] !== 'all') {
            $query->where('project_name', 'like', '%' . $this->filters['asset'] . '%');
        }

        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('sale_status', $this->filters['status']);
        }

        if (!empty($this->filters['manager']) && $this->filters['manager'] !== 'all') {
            $parts           = explode(' ', $this->filters['manager']);
            $first           = array_shift($parts);
            $last            = implode(' ', $parts);
            $managerUser     = Admin::where('name', $first)
                ->where('surname', $last)
                ->first();
            if ($managerUser) {
                $query->where('admin_id', $managerUser->id);
            }
        }

        if (!empty($this->filters['asset_type']) && $this->filters['asset_type'] !== 'all') {
            $query->where('type', $this->filters['asset_type']);
        }

        if (!empty($this->filters['asset_status']) && $this->filters['asset_status'] !== 'all') {
            $query->where('asset_status', $this->filters['asset_status']);
        }

        if (!empty($this->filters['agreement_status']) && $this->filters['agreement_status'] !== 'all') {
            $query->where('agreement_status', $this->filters['agreement_status']);
        }

        if (!empty($this->filters['agreement_date']) && $this->filters['agreement_date'] !== 'null') {
            if (!is_array($this->filters['agreement_date'])) {
                $createdDates = explode(',', $this->filters['agreement_date']);
            } else {
                $createdDates = $this->filters['agreement_date'];
            }
            if (isset($createdDates[0])) {
                $query->where('agreement_date', '>=', $createdDates[0]);
            }
            if (isset($createdDates[1])) {
                $query->where('agreement_date', '<=', $createdDates[1]);
            }
        }

        if (!empty($this->filters['investor']) && $this->filters['investor'] !== 'all') {
            $parts          = explode(' ', $this->filters['investor']);
            $first          = array_shift($parts);
            $last           = implode(' ', $parts);
            $investorUser   = Investor::where('name', $first)
                ->where('surname', $last)
                ->first();
            if ($investorUser) {
                $query->whereHas('investors', function ($q) use ($investorUser) {
                    $q->where('id', $investorUser->id);
                });
            }
        }

        if (\Auth::guard('investor')->check()) {
            $user = auth('investor')->user();
            $query->whereHas('investors', function ($q) use ($user) {
                $q->where('id', $user->getAuthIdentifier());
            });
        }

        // Payment‐date filter on the query side (to fetch only assets that have any payment/rental in that range or were created)
        if (
            isset($this->filters['payment_date']) &&
            $this->filters['payment_date'] !== 'null'
        ) {
            if (!is_array($this->filters['payment_date'])) {
                $parts = explode(',', $this->filters['payment_date']);
            } else {
                $parts = $this->filters['payment_date'];
            }
            $start = $parts[0] ?? null;
            $end   = $parts[1] ?? null;

            if ($start && $end) {
                $query->where(function ($q) use ($start, $end, $isDeveloper) {
                    if (! $isDeveloper) {
                        $q->whereBetween('created_at', [$start, $end])
                            ->orWhereHas('rentals', function ($q2) use ($start, $end) {
                                $q2->whereBetween('payment_date', [$start, $end]);
                            })
                            ->orWhereHas('renovationPayments', function ($q3) use ($start, $end) {
                                $q3->whereBetween('payment_date', [$start, $end]);
                            });
                    }

                    $q->orWhereHas('payments', function ($q4) use ($start, $end) {
                        $q4->whereBetween('payment_date', [$start, $end]);
                    });
                });
            }
        }

        $query->with([
            'investors',
            'rentals',
            'payments',
            'investments',
            'renovationPayments',
        ]);

        $assets = $query->get();

        $assets = $assets->map(function ($asset) {
            $investors = $asset->investors
                ->map(fn($inv) => $inv->name . ' ' . $inv->surname)
                ->implode(' / ');

            // Check if payment_date filter is active
            $paymentFilter = isset($this->filters['payment_date']) &&
                $this->filters['payment_date'] !== 'null';
            if ($paymentFilter) {
                if (!is_array($this->filters['payment_date'])) {
                    $parts = explode(',', $this->filters['payment_date']);
                } else {
                    $parts = $this->filters['payment_date'];
                }
                $start = $parts[0] ?? null;
                $end   = $parts[1] ?? null;
            } else {
                $start = $end = null;
            }

            //
            // Next Installment (only first payment within range, or fallback to existing logic)
            //
            $nextInstallment = '';
            if ($paymentFilter && $start && $end && $asset->agreement_status === 'Installments') {
                // Filter payments within [start, end], then pick earliest by payment_date
                $firstPayment = $asset->payments
                    ->filter(fn($p) =>
                        $p->payment_date >= $start &&
                        $p->payment_date <= $end
                    )
                    ->sortBy('payment_date')
                    ->first();

                if ($firstPayment) {
                    // If status = 0, use left_amount; if status = 1, use amount
                    $amt = ($firstPayment->status == 0)
                        ? number_format($firstPayment->left_amount, 0, ".", ",") . '$'
                        : number_format($firstPayment->amount, 0, ".", ",") . '$';

                    $nextInstallment = $firstPayment->payment_date . ' - ' . $amt;
                }
            } else {
                // Fallback: original “first unpaid installment” logic
                if (
                    $asset->agreement_status === 'Installments' &&
                    $asset->payments->where('status', 0)->count()
                ) {
                    $first = $asset->payments->where('status', 0)->first();
                    $now   = time();
                    $payTs = strtotime($first->payment_date);

                    if ($payTs < $now) {
                        $overdueSum = $asset->payments
                            ->where('status', 0)
                            ->filter(fn($p) => strtotime($p->payment_date) < $now)
                            ->sum('left_amount');

                        $nextInstallment = Carbon::parse($first->payment_date)
                                ->format('Y/m/d')
                            . ' - ' . number_format($overdueSum, 0, ".", ",") . '$';
                    } else {
                        $nextInstallment = $first->payment_date
                            . ' - ' . number_format($first->left_amount, 0, ".", ",") . '$';
                    }
                }
            }

            //
            // Next Rent (only first rental within range, or fallback to existing logic)
            //
            $nextRent = '';
            if ($paymentFilter && $start && $end && $asset->asset_status === 'Rented') {
                $firstRental = $asset->rentals
                    ->filter(fn($r) =>
                        $r->payment_date >= $start &&
                        $r->payment_date <= $end
                    )
                    ->sortBy('payment_date')
                    ->first();

                if ($firstRental) {
                    $amt = ($firstRental->status == 0)
                        ? number_format($firstRental->left_amount, 0, ".", ",") . '$'
                        : number_format($firstRental->amount, 0, ".", ",") . '$';

                    $nextRent = $firstRental->payment_date . ' - ' . $amt;
                }
            } else {
                // Fallback: original “first unpaid rent” logic
                if (
                    $asset->asset_status === 'Rented' &&
                    $asset->rentals->where('status', 0)->count()
                ) {
                    $first  = $asset->rentals->where('status', 0)->first();
                    $now    = time();
                    $rentTs = strtotime($first->payment_date);

                    if ($rentTs < $now) {
                        $overdueSum = $asset->rentals
                            ->where('status', 0)
                            ->filter(fn($r) => strtotime($r->payment_date) < $now)
                            ->sum('left_amount');

                        $nextRent = Carbon::parse($first->payment_date)
                                ->format('Y/m/d')
                            . ' - ' . number_format($overdueSum, 0, ".", ",") . '$';
                    } else {
                        $nextRent = $first->payment_date
                            . ' - ' . number_format($first->left_amount, 0, ".", ",") . '$';
                    }
                }
            }

            return [
                'Name'              => $asset->project_name,
                'City'              => $asset->city,
                'Investor'          => $investors,
                'Asset Type / Size' => $asset->flat_number
                    ? $asset->type . ' N' . $asset->flat_number . ' - ' . $asset->area . ' sq.m'
                    : '',
                'Agreement Status'  => $asset->agreement_status,
                'Next Installment'  => $nextInstallment,
                'Next Rent'         => $nextRent,
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
                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            },
        ];
    }
}
