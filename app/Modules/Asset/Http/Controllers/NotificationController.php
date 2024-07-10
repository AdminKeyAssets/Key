<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\Rental;
use App\Utilities\ServiceResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function payment()
    {
        $tenDaysFromNow = Carbon::now()->addDays(40)->format('Y/m/d');
        $user = auth()->user();
        $query = Payment::query();

        if (Auth::guard('investor')->check()) {
            $query->whereHas('asset', function ($q) use ($user) {
                $q->where('investor_id', $user->id);
            });
        } elseif (Auth::guard('admin')->check()) {
            if ($user->hasRole('Asset Manager')) {
                $query->whereHas('asset', function ($q) use ($user) {
                    $q->where('admin_id', $user->id);
                });
            }
        }

        $payments = $query->where('payment_date', '<=', $tenDaysFromNow)
            ->where('status', false)
            ->with('asset')
            ->get()
            ->map(function ($payment) {
                $investor = Investor::where('id', $payment->asset->investor_id)->first();
                return [
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'project_name' => $payment->asset->project_name,
                    'investor_name' => $investor->name,
                    'investor_surname' => $investor->surname,
                    'payment_date' => $payment->payment_date,
                ];
            });

        return ServiceResponse::jsonNotification('', 200, $payments);
    }

    public function rental()
    {
        $tenDaysFromNow = Carbon::now()->addDays(40)->format('Y/m/d');
        $user = auth()->user();
        $query = Rental::query();

        if (Auth::guard('investor')->check()) {
            $query->whereHas('asset', function ($q) use ($user) {
                $q->where('investor_id', $user->id);
            });
        } elseif (Auth::guard('admin')->check()) {
            if ($user->hasRole('Asset Manager')) {
                $query->whereHas('asset', function ($q) use ($user) {
                    $q->where('admin_id', $user->id);
                });
            }
        }

        $rentals = $query->where('payment_date', '<=', $tenDaysFromNow)
            ->where('status', false)
            ->with(['asset.tenant'])
            ->get()
            ->map(function ($rental) {
                $investor = Investor::where('id', $rental->asset->investor_id)->first();
                $tenant = $rental->asset->tenant->first();

                return [
                    'amount' => $rental->amount,
                    'currency' => $rental->currency,
                    'project_name' => $rental->asset->project_name,
                    'tenant_name' => $tenant->name,
                    'tenant_surname' => $tenant->surname,
                    'investor_name' => $investor->name,
                    'investor_surname' => $investor->surname,
                    'payment_date' => $rental->payment_date,
                ];
            });

        return ServiceResponse::jsonNotification('', 200, $rentals);
    }
}
