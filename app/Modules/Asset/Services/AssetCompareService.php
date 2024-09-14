<?php

namespace App\Modules\Asset\Services;

class AssetCompareService
{
    /**
     * @var CommentService
     */
    protected $commentService;

    /**
     * @param CommentService $commentService
     */
    public function __construct(
        CommentService $commentService
    )
    {
        $this->commentService = $commentService;
    }

    public function logAssetChanges($originalData, $updatedAsset, $adminId)
    {
        if (!$originalData) {
            return; // No need to check if this is a new asset
        }

        // Compare specific fields
        $fieldsToCheck = [
            'address' => 'Address',
            'cadastral_number' => 'Cadastral Number',
            'city' => 'City',
            'delivery_date' => 'Delivery Date',
            'area' => 'Area',
            'total_price' => 'Total Price',
            'price' => 'Price',
            'icon' => 'Icon',
            'floor_plan' => 'Floor Plan',
            'flat_plan' => 'Flat Plan',
            'ownership_certificate' => 'Ownership Certificate',
            'agreement_status' => 'Agreement Status',
            'current_value' => 'Current Value',
            'project_name' => 'Project Name',
            'project_description' => 'Project Description',
            'total_floors' => 'Total Floors',
            'delivery_condition_description' => 'Delivery Condition Description',
            'project_link' => 'Project Link',
            'location' => 'Location',
            'type' => 'Asset Type',
            'floor' => 'Floor',
            'flat_number' => 'Flat Number',
            'condition' => 'Delivery Condition',
            'agreement_date' => 'Agreement Date',
            'asset_status' => 'Asset Status',
        ];

        foreach ($fieldsToCheck as $field => $label) {
            if ($field === 'area' ||
                $field === 'total_price' ||
                $field === 'current_value' ||
                $field === 'price') {
                if ((float)$originalData[$field] !== (float)$updatedAsset->$field) {
                    $message = "{$label} for {$updatedAsset->projet_name} has been updated!";
                    // Log the change as a comment
                    $this->commentService->logChange($message, $updatedAsset->id, $adminId);
                }
            } else {
                if ($originalData[$field] !== $updatedAsset->$field) {
                    $message = "{$label} for {$updatedAsset->projet_name} has been updated!";
                    // Log the change as a comment
                    $this->commentService->logChange($message, $updatedAsset->id, $adminId);
                }
            }
        }
    }

    public function logPaymentChanges($originalPayments, $newPayments, $asset, $adminId)
    {
        $paymentSchedule = false;

        if(count($originalPayments) != count($newPayments)){
            $paymentSchedule = true;
        }

        foreach ($originalPayments as $key => $originalPayment) {
            if (!isset($newPayments[$key]) || (float)$originalPayment['amount'] !== (float)$newPayments[$key]['amount'] || $originalPayment['payment_date'] !== $newPayments[$key]['payment_date']) {

                $paymentSchedule = true;
            }
        }

        // Handle new payments added
        foreach ($newPayments as $key => $newPayment) {
            if (!isset($originalPayments[$key])) {

                $paymentSchedule = true;
            }
        }

        if($paymentSchedule){
            $this->commentService->logChange('Payment schedule for' . $asset->project_name . ' has been updated!', $asset->id, $adminId);
        }
    }

    public function logRentalPaymentChanges($originalPayments, $newPayments, $asset, $adminId)
    {
        $rentalScheduleChanged = false;

        if(count($originalPayments) != count($newPayments)){
            $rentalScheduleChanged = true;
        }

        foreach ($originalPayments as $key => $originalPayment) {
            if (!isset($newPayments[$key]) || (float)$originalPayment['amount'] !== (float)$newPayments[$key]['amount'] || $originalPayment['payment_date'] !== $newPayments[$key]['payment_date']) {
                $rentalScheduleChanged = true;
            }
        }

        // Handle new payments added
        foreach ($newPayments as $key => $newPayment) {
            if (!isset($originalPayments[$key])) {
                $rentalScheduleChanged = true;
            }
        }

        if($rentalScheduleChanged){
            $this->commentService->logChange('Rental schedule for' . $asset->project_name . ' has been updated!', $asset->id, $adminId);
        }
    }

    public function logGalleryChanges($originalGallery, $newGallery, $asset, $adminId)
    {
        $galeryChanged = false;
        foreach ($originalGallery as $key => $originalImage) {
            if (!isset($newGallery[$key]) || $originalImage['image'] !== $newGallery[$key]['image']) {
                $galeryChanged = true;
            }
        }

        // Handle new images added
        foreach ($newGallery as $key => $newImage) {
            if (!isset($originalGallery[$key])) {
                $galeryChanged = true;
            }
        }

        if($galeryChanged){
            $this->commentService->logChange('Gallery for ' . $asset->project_name . ' has been updated!', $asset->id, $adminId);
        }
    }

    public function logAgreementChanges($originalAgreements, $newAgreements, $asset, $adminId)
    {
        $agreementUpdated = false;
        foreach ($originalAgreements as $key => $originalAgreement) {
            if (!isset($newAgreements[$key]) || $originalAgreement['name'] !== $newAgreements[$key]['name']) {
                $agreementUpdated = true;
            }
        }

        // Handle new agreements added
        foreach ($newAgreements as $key => $newAgreement) {
            if (!isset($originalAgreements[$key])) {
                $agreementUpdated = true;
            }
        }
        if($agreementUpdated){
            $this->commentService->logChange('Agreements for ' . $asset->project_name . ' have been updated!', $asset->id, $adminId);
        }
    }

    public function logInformationsChanges($originalInformations, $newInformations, $asset, $adminId)
    {
        $informationChanged = false;
        foreach ($originalInformations as $key => $originalInformation) {
            if (!isset($newInformations[$key]) || $originalInformation['value'] !== $newInformations[$key]['value']) {
                $informationChanged = true;
            }
        }

        // Handle new informations added
        foreach ($newInformations as $key => $newInformation) {
            if (!isset($originalInformations[$key])) {
                $informationChanged = true;
            }
        }

        if($informationChanged){
            $this->commentService->logChange('Extra Details for ' . $asset->project_name . ' have been updated!', $asset->id, $adminId);
        }
    }

    public function logAttachmentChanges($originalAttachments, $newAttachments, $asset, $adminId)
    {
        $attachmentsChanged = false;
        foreach ($originalAttachments as $key => $originalAttachment) {
            if (!isset($newAttachments[$key]) || $originalAttachment['image'] !== $newAttachments[$key]['image']) {
              $attachmentsChanged = true;
            }
        }

        // Handle new attachments added
        foreach ($newAttachments as $key => $newAttachment) {
            if (!isset($originalAttachments[$key])) {
                $attachmentsChanged = true;
            }
        }

        if($attachmentsChanged){
            $this->commentService->logChange('Asset Photos for' . $asset->project_name . ' have been updated!', $asset->id, $adminId);
        }
    }

}
