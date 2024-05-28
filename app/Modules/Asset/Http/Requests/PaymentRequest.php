<?php

namespace App\Modules\Asset\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'month' => ['required','integer'],
            'payment_date' => 'required',
            'amount' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return [
            'month.required' => ':attribute can not be empty.',
            'month.integer' => ':attribute should be number.',
            'payment_date.required' => ':attribute can not be empty.',
            'amount.required' => ':attribute can not be empty.',
            'amount.regex' => ':attribute should be double.',
        ];
    }
}
