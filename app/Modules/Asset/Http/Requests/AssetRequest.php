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
            'project_name' => 'required',
            'address' => 'required',
            'cadastral_number' => 'required',
            'investor_id' => 'required',
            'city' => 'required',
            'currency' => 'required',
            'area' => ['required', 'numeric'],
            'total_price' => ['required', 'numeric'],

            'type' => 'required',
            'floor' => 'integer',
            'flat_number' => 'integer',
            'price' => 'required|numeric',
            'condition' => 'required',
            'asset_status' => 'required',
            'tenant.name' => 'required_if:asset_status,Rented',
            'tenant.surname' => 'required_if:asset_status,Rented',
            'tenant.id_number' => 'required_if:asset_status,Rented|numeric',
            'tenant.citizenship' => 'required_if:asset_status,Rented',
            'tenant.email' => 'required_if:asset_status,Rented|email',
            'tenant.prefix' => 'required_if:asset_status,Rented',
            'tenant.phone' => 'required_if:asset_status,Rented|numeric',
            'tenant.agreement_date' => 'required_if:asset_status,Rented',
            'tenant.agreement_term' => 'required_if:asset_status,Rented|numeric',
            'tenant.monthly_rent' => 'required_if:asset_status,Rented|numeric',
            'tenant.currency' => 'required_if:asset_status,Rented',

            'agreement_date' => 'required',
            'agreement_status' => 'required',
            'total_agreement_price' => 'required_if:agreement_status,Installments|numeric',
            'first_payment_date' => 'required_if:agreement_status,Installments',
            'period' => 'required_if:agreement_status,Installments|numeric',
        ];
    }

    public function messages()
    {
        return [
            'project_name.required' => 'Project Name can not be empty.',
            'address.required' => 'Address can not be empty.',
            'cadastral_number.required' => 'Cadastral Number can not be empty.',
            'investor_id.required' => 'Investor can not be empty.',
            'city.required' => 'City can not be empty.',
            'area.required' => 'Area can not be empty.',
            'area.numeric' => 'Area should be numeric.',
            'currency.required' => 'Currency can not be empty.',
            'total_price.numeric' => 'Total Price should be numeric.',
            'total_price.required' => 'Total Price can not be empty.',
            'price.numeric' => 'Price should be numeric.',
            'price.required' => 'Price can not be empty.',
            'type.required' => 'Please select Asset Type.',
            'floor.numeric' => 'Floor should be numeric.',
            'flat_number.numeric' => 'Flat Number should be numeric.',
            'condition.required' => 'Please select Delivery Condition',
            'asset_status.required' => 'Please select Asset Status',
            'tenant.name.required_if' => 'The tenant Name is required when asset status is rented.',
            'tenant.surname.required_if' => 'The tenant Surname is required when asset status is rented.',
            'tenant.email.required_if' => 'The tenant Email is required when asset status is rented.',
            'tenant.email.email' => 'The tenant Email must be a valid email address.',
            'tenant.pid.required_if' => 'The tenant ID Number is required when asset status is rented.',
            'tenant.pid.numeric' => 'The tenant ID Number must be numeric.',
            'tenant.citizenship.required_if' => 'The tenant Citizenship is required when asset status is rented.',
            'tenant.prefix.required_if' => 'The tenant Prefix is required when asset status is rented.',
            'tenant.phone.required_if' => 'The tenant Phone is required when asset status is rented.',
            'tenant.phone.numeric' => 'The tenant Phone must be numeric.',
            'tenant.agreement_date.required_if' => 'The tenant Agreement Date is required when asset status is rented.',
            'tenant.agreement_term.required_if' => 'The tenant Agreement Term is required when asset status is rented.',
            'tenant.agreement_term.numeric' => 'The tenant Agreement Term must be numeric.',
            'tenant.monthly_rent.required_if' => 'The tenant Monthly Rent is required when asset status is rented.',
            'tenant.monthly_rent.numeric' => 'The tenant Monthly Rent must be numeric.',
            'tenant.currency.required_if' => 'The tenant Currency is required when asset status is rented.',
            'agreement_date.required' => 'Please select Agreement Date',
            'agreement_status.required' => 'Please select Agreement Status',
            'total_agreement_price.required_if' => 'The Total Agreement Price is required when agreement status is Installments.',
            'total_agreement_price.numeric' => 'The Total Agreement Price must be numeric.',
            'first_payment_date.required_if' => 'The First Payment Date is required when agreement status is Installments.',
            'period.required_if' => 'The Period is required when agreement status is Installments.',
            'period.numeric' => 'The Period must be numeric.',
            'current_value.numeric' => 'The Current must be numeric.',
        ];
    }
}
