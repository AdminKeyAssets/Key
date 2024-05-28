<?php

namespace App\Modules\Asset\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaseRequest extends FormRequest
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
            'date_from' => 'required',
            'date_to' => 'required',
            'price' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return [
            'date_from.required' => 'Date From can not be empty.',
            'date_to.required' => 'Date To can not be empty.',
            'price.required' => 'Price can not be empty.',
            'price.regex' => 'Price should be double.',
        ];
    }
}
