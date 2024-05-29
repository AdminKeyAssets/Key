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
            'currency' => 'required',
            'amount' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return [
            'month.required' => 'Month can not be empty.',
            'month.integer' => 'Month should be number.',
            'payment_date.required' => 'Payment Date can not be empty.',
            'amount.required' => 'Amount can not be empty.',
            'currency.required' => 'Currency can not be empty.',
            'amount.regex' => 'Amount should be double.',
        ];
    }
}
