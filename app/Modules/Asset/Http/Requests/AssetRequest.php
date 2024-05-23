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
            'area' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
            'total_price' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name can not be empty.',
            'address.required' => 'Address can not be empty.',
            'cadastral_number.required' => 'Cadastral Number can not be empty.',
            'investor_id.required' => 'Investor can not be empty.',
            'city.required' => 'City can not be empty.',
            'delivery_date.required' => 'Delivery Date can not be empty.',
            'area.required' => 'Area can not be empty.',
            'area.regex' => 'Area should be double.',
            'total_price.regex' => 'Price should be double.',
            'total_price.required' => 'Price can not be empty.',
        ];
    }
}
