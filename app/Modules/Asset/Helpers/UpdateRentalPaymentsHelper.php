<?php

namespace App\Modules\Asset\Helpers;

use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\Tenant;

class UpdateRentalPaymentsHelper
{
    public function updateRentalPayments(Asset $asset, $amount)
    {
        $rentals = $asset->rentals()->where('status', 0)->orderBy('id')->get();

        foreach ($rentals as $rental) {
            if ($amount <= 0) {
                break;
            }

            if ($amount >= $rental->left_amount) {
                $amount -= $rental->left_amount;
                $rental->status = 1;
                $rental->left_amount = 0;
            } else {
                $rental->left_amount -= $amount;
                $amount = 0;
            }

            $rental->save();
        }
    }

    public function recalculateRentalPayments(Asset $asset)
    {
        $tenant = Tenant::where('asset_id', $asset->id)->where('status', 1)->orderByDesc('id')->first();
        $totalPaid = $asset->rentalPaymentsHistories()->where('tenant_id', $tenant->id)->sum('amount');

        $this->updateRentalPayments($asset, $totalPaid);
    }

    public function recalculateRentalPaymentsAfterDeletion(Asset $asset, $amount)
    {
        $rentals = $asset->rentals()->orderByDesc('id')->get();

        foreach ($rentals as $rental) {
            if ($amount <= 0) {
                break;
            }

            if ($rental->left_amount == 0 && $rental->status == 1) {
                $amount -= $rental->amount;
                $rental->status = 0;
                $rental->left_amount = $rental->amount;
            } elseif ($rental->left_amount < $rental->amount && $rental->status == 0) {
                // Partially paid payment, revert to original amount
                $remaining = $rental->amount - $rental->left_amount;
                if ($amount >= $remaining) {
                    $rental->left_amount += $remaining;
                    $amount -= $remaining;
                } else {
                    $rental->left_amount += $amount;
                    $amount = 0.00;
                    if (round($rental->left_amount, 2) <= 0.00) {
                        $rental->status = 1;
                        $rental->left_amount = 0.00;
                    }
                }
            }
            $rental->save();
        }
    }

    public function recalculateRentalPaymentsAfterEdit(Asset $asset, $oldAmount, $newAmount)
    {
        if ($newAmount > $oldAmount) {
            // Additional amount to be paid
            $additionalAmount = $newAmount - $oldAmount;
            $this->updateRentalPayments($asset, $additionalAmount);
        } else {
            // Reduce amount from payments
            $reduceAmount = $oldAmount - $newAmount;
            $this->recalculateRentalPaymentsAfterDeletion($asset, $reduceAmount);
        }
    }
}
