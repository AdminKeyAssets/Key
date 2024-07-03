<?php

namespace App\Modules\Asset\Helpers;

use App\Modules\Asset\Models\Asset;

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
        $totalPaid = $asset->rentalPaymentsHistories()->sum('amount');

        $this->updateRentalPayments($asset, $totalPaid);
    }
}
