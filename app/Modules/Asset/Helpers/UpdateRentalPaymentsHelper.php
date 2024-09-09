<?php

namespace App\Modules\Asset\Helpers;

use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use App\Modules\Asset\Models\Tenant;

class UpdateRentalPaymentsHelper
{
    /**
     * Update rental payments by applying an amount, starting from a specific month,
     * and save the allocation to the PaymentHistory in JSON format.
     *
     * @param Asset $asset
     * @param float $amount
     * @param RentalPaymentsHistory $paymentHistory The payment history to save applied_to_months
     * @param int|null $startMonth (optional) The month to start applying the payment from.
     * @return array The breakdown of how the payment was applied.
     */
    public function updateRentalPayments(Asset $asset, $amount, RentalPaymentsHistory $paymentHistory, $startMonth = null)
    {
        $rentals = $asset->rentals()->where('status', 0)->orderBy('id')->get();
        $appliedToMonths = []; // To store the breakdown of payments applied to each month

        if ($startMonth !== null) {
            // Move the specified month to the front of the collection
            $rentals = $rentals->sortBy(function ($rental) use ($startMonth) {
                return ($rental->number == $startMonth) ? -1 : $rental->id;
            });
        }

        foreach ($rentals as $rental) {
            if ($amount <= 0) {
                break;
            }

            if ($amount >= $rental->left_amount) {
                $appliedToMonths[$rental->number] = $rental->left_amount;
                $amount -= $rental->left_amount;
                $rental->status = 1; // Mark as paid
                $rental->left_amount = 0;
            } else {
                $appliedToMonths[$rental->number] = $amount;
                $rental->left_amount -= $amount;
                $amount = 0;
            }

            $rental->save();
        }

        // Save the applied breakdown to the payment history in JSON format
        $paymentHistory->applied_to_months = json_encode($appliedToMonths);
        $paymentHistory->save();

        return $appliedToMonths;
    }

    public function recalculateRentalPaymentsAfterDeletion(Asset $asset, RentalPaymentsHistory $paymentHistory)
    {
        // Get the breakdown of applied payments from the history
        $appliedToMonths = json_decode($paymentHistory->applied_to_months, true);

        // If applied_to_months is empty or null, calculate how the amount was likely applied
        if (empty($appliedToMonths)) {
            $appliedToMonths = $this->calculateAppliedMonthsForOldPayments($asset, $paymentHistory->amount);
        }

        // Process rentals in reverse order to restore the payments starting from the latest affected month
        foreach (array_reverse($appliedToMonths, true) as $monthNumber => $appliedAmount) {
            $rental = $asset->rentals()->where('number', $monthNumber)->first();

            if ($rental) {
                // Restore the rental's left_amount by adding back the amount that was applied during the payment
                $rental->left_amount += $appliedAmount;

                // Ensure the left_amount does not exceed the original amount of the rental
                if ($rental->left_amount > $rental->amount) {
                    $rental->left_amount = $rental->amount;
                }

                // If the rental has any left_amount, mark it as unpaid
                if ($rental->left_amount > 0) {
                    $rental->status = 0;  // Mark as unpaid
                } else {
                    $rental->status = 1;  // If left_amount is 0, it remains paid
                }

                $rental->save();
            }
        }

        $paymentHistory->delete();
    }


    /**
     * Calculate the allocation of payments for old payment history records that do not have applied_to_months.
     *
     * @param Asset $asset
     * @param float $amount
     * @return array The breakdown of how the payment was likely applied to each month.
     */
    private function calculateAppliedMonthsForOldPayments(Asset $asset, $amount)
    {
        $rentals = $asset->rentals()->orderByDesc('id')->get();
        $appliedToMonths = [];

        foreach ($rentals as $rental) {
            if ($amount <= 0) {
                break;
            }

            if ($rental->amount > $rental->left_amount) {
                $amountDiff = $rental->amount - $rental->left_amount;
                $appliedToMonths[$rental->number] = $amountDiff;
                $amount -= $amountDiff;
            }
        }

        return $appliedToMonths;
    }

    /**
     * Edit rental payments after a modification, adjusting the payment breakdown and recalculating amounts.
     *
     * @param Asset $asset
     * @param RentalPaymentsHistory $paymentHistory
     * @param float $oldAmount
     * @param float $newAmount
     * @param int|null $startMonth (optional) The month to start recalculating from.
     */
    public function recalculateRentalPaymentsAfterEdit(Asset $asset, RentalPaymentsHistory $paymentHistory, $oldAmount, $newAmount, $startMonth = null)
    {
        // Retrieve the original breakdown of payments from the payment history
        $appliedToMonths = json_decode($paymentHistory->applied_to_months, true);

        if (empty($appliedToMonths)) {
            // Handle the case where old records do not have applied_to_months data
            $appliedToMonths = $this->calculateAppliedMonthsForOldPayments($asset, $oldAmount);
        }

        if ($newAmount > $oldAmount) {
            // Increase the amount (apply additional payment)
            $additionalAmount = $newAmount - $oldAmount;
            $newAppliedToMonths = $this->updateRentalPayments($asset, $additionalAmount, $paymentHistory, $startMonth);

            // Merge the old and new allocations
            $finalAppliedToMonths = array_merge($appliedToMonths, $newAppliedToMonths);

            // Update payment history with the new breakdown
            $paymentHistory->applied_to_months = json_encode($finalAppliedToMonths);
            $paymentHistory->amount = $newAmount;
            $paymentHistory->save();
        } else {
            // Reduce amount from payments
            $reduceAmount = $oldAmount - $newAmount;

            // Revert based on the current applied amounts
            $this->recalculateRentalPaymentsAfterDeletion($asset, $paymentHistory);

            // Apply the new reduced amount and save the updated breakdown
            $newAppliedToMonths = $this->updateRentalPayments($asset, $newAmount, $paymentHistory, $startMonth);

            // Update the payment history
            $paymentHistory->applied_to_months = json_encode($newAppliedToMonths);
            $paymentHistory->amount = $newAmount;
            $paymentHistory->save();
        }
    }
}
