<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\CurrentValue;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\Rental;
use App\Utilities\ServiceResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CurrentValueController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'value' => 'required',
        ]);

        $currentValue = CurrentValue::findOrFail($id);

        if ($request->has('attachment')) {
            if (gettype($request->attachment) == 'string') {
                $path = $request->attachment;
            }
            else{
                Storage::disk('public')->delete($currentValue->attachment);
                $originalFileName = $request->attachment->getClientOriginalName();
                $path = $request->attachment->storeAs('uploads', $originalFileName, 'public');
                $path = Storage::url($path);
            }

            $validatedData['attachment'] = $path;
        }
        $currentValue->update($validatedData);
        return response()->json(['message' => 'Current Value updated successfully', 'data' => $currentValue->toArray()], 200);
    }

    // Delete a current value
    public function destroy($id)
    {
        $currentValue = CurrentValue::findOrFail($id);
        if ($currentValue->attachment) {
            Storage::disk('public')->delete($currentValue->attachment);
        }

        $currentValue->delete();

        return response()->json(['message' => 'Current Value deleted successfully'], 200);
    }
}
