<?php

namespace App\Modules\Asset\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'address' => 'required',
            'cadastral_number' => 'required',
//            'document' => 'required',
            'investor_id' => 'required',
            'city' => 'required',
            'delivery_date' => 'required',
            'area' => 'required',
            'total_price' => 'required'
        ];
    }
}
