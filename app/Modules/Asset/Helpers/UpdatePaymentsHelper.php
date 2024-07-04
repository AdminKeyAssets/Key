<?php

namespace App\Modules\Asset\Helpers;

use App\Modules\Asset\Models\Asset;

class UpdatePaymentsHelper
{
    public function updatePayments(Asset $asset, $amount)
    {
        $payments = $asset->payments()->where('status', 0)->orderBy('id')->get();

        foreach ($payments as $payment) {
            if ($amount <= 0) {
                break;
            }

            if ($amount >= $payment->left_amount) {
                $amount -= $payment->left_amount;
                $payment->status = 1;
                $payment->left_amount = 0;
            } else {
                $payment->left_amount -= $amount;
                $amount = 0;
            }
            $payment->update();
        }

        if ($asset->payments()->where('status', 0)->count() === 0) {
            $asset->agreement_status = 'Complete';
            $asset->update();
        }
    }

    public function recalculatePayments(Asset $asset)
    {
        $totalPaid = $asset->paymentsHistories()->sum('amount');

        $this->updatePayments($asset, $totalPaid);
    }
}
