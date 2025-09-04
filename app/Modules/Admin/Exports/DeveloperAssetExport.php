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

class DeveloperAssetExport implements FromCollection, WithHeadings, WithEvents
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Asset::query()->orderByDesc('id');
        
        // Get the developer user
        $user = \Auth::guard('developer')->user();
        $query->whereIn('project_name', $user->assets()->pluck('asset_name')->toArray())
            ->where('developer_access', 1);
        
        // Apply status filtering like in the myassets method
        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $statusFilter = $this->filters['status'];
            if ($statusFilter === 'active') {
                $query->where('is_archived', false);
                $query->where('sale_status', 'active');
            } elseif ($statusFilter === 'archived') {
                $query->where('is_archived', true);
            } elseif ($statusFilter === 'sold') {
                $query->where('sale_status', 'sold');
            }
        } else {
            // Default behavior if no status filter (match active assets)
            $query->where('is_archived', false);
            $query->where('sale_status', 'active');
        }

        // Apply asset‐level filters
        if (!empty($this->filters['asset']) && $this->filters['asset'] !== 'all') {
            $query->where('project_name', 'like', '%' . $this->filters['asset'] . '%');
        }

        if (!empty($this->filters['manager']) && $this->filters['manager'] !== 'all') {
            $parts = explode(' ', $this->filters['manager']);
            $first = array_shift($parts);
            $last = implode(' ', $parts);
            $managerUser = Admin::where('name', $first)
                ->where('surname', $last)
                ->first();
            if ($managerUser) {
                $query->where('admin_id', $managerUser->id);
            }
        }

        if (!empty($this->filters['asset_type']) && $this->filters['asset_type'] !== 'all') {
            $query->where('type', $this->filters['asset_type']);
        }
        
        if (!empty($this->filters['city']) && $this->filters['city'] !== 'all') {
            $query->where('city', $this->filters['city']);
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
            $parts = explode(' ', $this->filters['investor']);
            $first = array_shift($parts);
            $last = implode(' ', $parts);
            $investorUser = Investor::where('name', $first)
                ->where('surname', $last)
                ->first();
            if ($investorUser) {
                $query->whereHas('investors', function ($q) use ($investorUser) {
                    $q->where('id', $investorUser->id);
                });
            }
        }

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
            $end = $parts[1] ?? null;

            if ($start && $end) {
                $query->where(function ($q) use ($start, $end) {
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
            'paymentsHistories',
            'investments',
            'renovationPayments',
        ]);

        $assets = $query->get();

        $assets = $assets->map(function ($asset) {
            $investors = $asset->investors
                ->map(fn($inv) => $inv->name . ' ' . $inv->surname)
                ->implode(' / ');

            // Calculate paid amount and percentage
            $payments = $asset->paymentsHistories ?: collect([]);

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
                $end = $parts[1] ?? null;

                // Apply date filtering to payments if needed
                if ($start) {
                    $payments = $payments->filter(function ($payment) use ($start) {
                        return isset($payment->payment_date) && strtotime($payment->payment_date) >= strtotime($start);
                    });
                }
                if ($end) {
                    $payments = $payments->filter(function ($payment) use ($end) {
                        return isset($payment->payment_date) && strtotime($payment->payment_date) <= strtotime($end);
                    });
                }
            } else {
                $start = $end = null;
            }

            // Calculate paid amount based on agreement status
            if ($asset->agreement_status === 'Installments') {
                $paid = $payments->sum('amount');
            } else {
                $paid = $asset->total_price;
            }

            // Calculate percentage
            $paidPercent = $asset->total_price > 0
                ? (fmod(($paid / $asset->total_price) * 100, 1) == 0
                    ? number_format(($paid / $asset->total_price) * 100, 0)
                    : number_format(($paid / $asset->total_price) * 100, 2))
                : '0';

            // Create paid_formatted string
            $paid_formatted = number_format($paid, 0, ".", ",") . '$ - ' . $paidPercent . '%';

            //
            // Next Installment (only first payment within range, or fallback to existing logic)
            //
            $nextInstallment = '';
            if ($paymentFilter && $start && $end && $asset->agreement_status === 'Installments') {
                // Filter payments within [start, end], then pick earliest by payment_date
                $firstPayment = $asset->payments
                    ->filter(fn($p) => $p->payment_date >= $start &&
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
                    $now = time();
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


            return [
                'Name' => $asset->project_name,
                'City' => $asset->city,
                'Investor' => $investors,
                'Asset Type / Size' => $asset->type . ' ' .
                    ($asset->flat_number ? ' N' . $asset->flat_number . ' - ' : '') .
                    ($asset->area ? $asset->area . ' sq.m' : ''),
                'Purchase Price' => number_format($asset->total_price) . '$',
                'Paid' => $paid_formatted,
                'Agreement Status' => $asset->agreement_status,
                'Next Installment' => $nextInstallment,
                'Current Value' => number_format($asset->current_value) . '$',
                'Capital Gain' => number_format($asset->current_value - $asset->total_price) . '$',
                'Manager' => $asset->investors->first()->admin->name . ' ' . $asset->investors->first()->admin->surname,
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
            'Purchase Price',
            'Paid',
            'Agreement Status',
            'Next Installment',
            'Current Value',
            'Capital Gain',
            'Manager',
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
