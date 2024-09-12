<?php

namespace App\Modules\Lead\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
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
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge(
            array_map(function ($value) {
                return $value === 'null' ? null : $value;
            }, $this->all())
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'project' => 'required',
            'investor' => 'required',
            'type' => 'required',
            'size' => 'required',
            'price' => 'required',
            'total_price' => 'required',
            'agreement_status' => 'required',
            'down_payment' => 'required_if:agreement_status,Installments',
            'period' => 'required_if:agreement_status,Installments',
        ];
    }

    public function messages()
    {
        return [
            'project.required' => 'Project can not be empty.',
            'investor.required' => 'Investor can not be empty.',
            'type.required' => 'Type can not be empty.',
            'size.required' => 'Size can not be empty.',
            'price.required' => 'Price can not be empty.',
            'total_price.required' => 'Total Price can not be empty.',
            'agreement_status.required' => 'Agreements Status can not be empty.',
            'down_payment.required_if' => 'The Donw Payment is required when agreement status is Installments.',
            'period.required_if' => 'The Period is required when agreement status is Installments.',
        ];
    }
}
