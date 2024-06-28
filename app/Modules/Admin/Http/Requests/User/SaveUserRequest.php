<?php

namespace App\Modules\Admin\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SaveUserRequest extends FormRequest
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

        $passwordRequired = 'required';

        if ( !empty($this->request->all()['id'])) {
            $passwordRequired = 'nullable';
        }

        return [
            'name'  => 'required',
            'email' => 'required',
            'phone' => 'required',
            'prefix' => 'required',
            'password'  => [$passwordRequired,
//                'min:8',
//                'regex:/[a-z]/',
//                'regex:/[A-Z]/',
//                'regex:/[0-9]/'
            ],
        ];
    }
}
