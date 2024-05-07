<?php

namespace App\Modules\Admin\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class SaveProfileRequest extends FormRequest
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
            'name'  => 'required',
            'email' => 'required',
            'password'  => ['nullable','min:8','regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/'],
        ];
    }
}
