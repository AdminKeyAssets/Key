<?php

namespace App\Modules\Asset\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestmentRequest extends FormRequest
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
            'date' => 'required',
            'currency' => 'required',
            'status' => 'required',
            'amount' => ['required', 'numeric'],
        ];
    }

    /**
     * @return string[]
     */
    public function messages()
    {
        return [
            'date.required' => 'Payment Date can not be empty.',
            'amount.required' => 'Amount can not be empty.',
            'amount.numeric' => 'Amount should be numeric.',
            'currency.required' => 'Currency can not be empty.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(
            array_map(function ($value) {
                return $value === 'null' ? null : $value;
            }, $this->all())
        );
    }
}
