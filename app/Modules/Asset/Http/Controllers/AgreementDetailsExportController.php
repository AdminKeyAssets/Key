<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Exports\AgreementDetailsExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Asset\Models\Asset;
use App\Modules\Admin\Models\User\Investor;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Storage;

class AgreementDetailsExportController extends BaseController
{
    /**
     * Export agreement details as PDF
     *
     * @param Request $request
     * @param int $assetId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAgreementDetails(Request $request, $assetId)
    {
        // Validate the request
        $request->validate([
            'id' => 'required',
            'assetType' => 'nullable|string',
            'area' => 'nullable',
            'flatNumber' => 'nullable',
            'price' => 'nullable|numeric',
            'totalPrice' => 'nullable|numeric',
            'period' => 'nullable',
            'payments' => 'nullable|array',
            'paymentsHistories' => 'nullable|array'
        ]);

        // Get asset information
        $asset = Asset::findOrFail($assetId);
        $assetName = $asset->project_name;

        $investors = $asset->investors;
        $investorNames = [];
        foreach ($investors as $investor) {
            $investorNames[] = $investor->name . ' ' . $investor->surname;
            $investorEmails[] = $investor->email;
            $investorPids[] = $investor->pid;
        }
        $investorNames = implode(' / ', $investorNames);
        $investorEmails = implode(' / ', $investorEmails);
        $investorPids = implode(' / ', $investorPids);

        // Prepare data for PDF
        $data = [
            'investorNames' => $investorNames,
            'investorEmail' => $investorEmails,
            'investorId' => $investorPids,
            'assetName' => $assetName,
            'assetType' => $request->assetType,
            'area' => $request->area,
            'flatNumber' => $request->flatNumber,
            'price' => $request->price,
            'totalPrice' => $request->totalPrice,
            'period' => $request->period,
            'payments' => $request->payments,
            'paymentsHistories' => $request->paymentsHistories
        ];

        // Generate PDF
        $pdf = PDF::loadView('asset::admin.asset.agreement.pdf_export', $data);

        // Set PDF options
        $pdf->setPaper('a4', 'portrait');

        // Generate a unique filename
        $filename = 'agreement_details_' . $assetId . '_' . date('Y-m-d_H-i-s') . '.pdf';

        // Return the PDF for download
        return $pdf->download($filename);
    }
}
