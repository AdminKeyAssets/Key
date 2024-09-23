<?php

namespace App\Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManagerRequest extends FormRequest
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
            'manager_id' => 'required',
            'investor_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'manager_id.required' => 'Manager can not be empty.',
            'investor_id.required' => 'Investor can not be empty.',
        ];
    }
}
