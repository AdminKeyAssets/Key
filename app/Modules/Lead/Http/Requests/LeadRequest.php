<?php

namespace App\Modules\Lead\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadRequest extends FormRequest
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
            'name' => 'required',
            'surname' => 'required',
            'phone' => 'required',
            'prefix' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name can not be empty.',
            'surname.required' => 'Surname can not be empty.',
            'email.required' => 'Email can not be empty.',
            'phone.required' => 'Phone Number can not be empty.',
            'prefix.required' => 'Prefix can not be empty.',
            'email.email' => 'Email value is not Email.',
        ];
    }
}
