<?php

namespace App\Modules\Asset\Helpers;

use App\Modules\Asset\Models\Asset;

class UpdatePaymentsHelper
{
    public function updatePayments(Asset $asset, $amount)
    {
        $payments = $asset->payments()->where('status', 0)->orderBy('id')->get();

        foreach ($payments as $payment) {
            if ($amount <= 0.00) {
                break;
            }

            if ($amount >= $payment->left_amount) {
                $amount -= $payment->left_amount;
                $payment->status = 1;
                $payment->left_amount = 0.00;
            } else {
                $payment->left_amount -= $amount;
                $amount = 0.00;
                if (round($payment->left_amount, 2) <= 0.00) {
                    $payment->status = 1;
                    $payment->left_amount = 0.00;
                }
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

    public function recalculatePaymentsAfterDeletion(Asset $asset, $amount)
    {
        $payments = $asset->payments()->orderByDesc('id')->get();

        foreach ($payments as $payment) {
            if ($amount <= 0) {
                break;
            }

            if ($payment->left_amount == 0 && $payment->status == 1) {
                $amount -= $payment->amount;
                $payment->status = 0;
                $payment->left_amount = $payment->amount;
            } elseif ($payment->left_amount < $payment->amount && $payment->status == 0) {
                // Partially paid payment, revert to original amount
                $remaining = $payment->amount - $payment->left_amount;
                if ($amount >= $remaining) {
                    $payment->left_amount += $remaining;
                    $amount -= $remaining;
                } else {
                    $payment->left_amount += $amount;
                    $amount = 0;
                }
            }
            $payment->save();
        }
    }

    public function recalculatePaymentsAfterEdit(Asset $asset, $oldAmount, $newAmount)
    {
        if ($newAmount > $oldAmount) {
            // Additional amount to be paid
            $additionalAmount = $newAmount - $oldAmount;
            $this->updatePayments($asset, $additionalAmount);
        } else {
            // Reduce amount from payments
            $reduceAmount = $oldAmount - $newAmount;
            $this->recalculatePaymentsAfterDeletion($asset, $reduceAmount);
        }
    }
}
