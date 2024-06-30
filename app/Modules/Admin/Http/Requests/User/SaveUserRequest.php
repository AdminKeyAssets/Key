<?php

namespace App\Modules\Admin\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        if (!empty($this->request->all()['id'])) {
            $passwordRequired = 'nullable';
        }

        return [
            'name' => 'required',
            'surname' => 'required',
            'email' => ['required', !empty($this->request->all()['id']) ? Rule::unique('admins', 'pid')->ignore($this->request->all()['id']) : 'unique:admins,email', 'email'],
            'pid' => ['required', !empty($this->request->all()['id']) ? Rule::unique('admins', 'pid')->ignore($this->request->all()['id']) : 'unique:admins,pid', 'numeric'],
            'phone' => 'required',
            'prefix' => 'required',
            'roles' => 'required',
            'password' => [$passwordRequired,
//                'min:8',
//                'regex:/[a-z]/',
//                'regex:/[A-Z]/',
//                'regex:/[0-9]/'
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.numeric' => 'Name can not be empty.',
            'surname.required' => 'Surname can not be empty.',
            'email.required' => 'Email can not be empty.',
            'email.email' => 'Missing @ in Email.',
            'pid.required' => 'ID/Passport Number can not be empty.',
            'pid.numeric' => 'ID/Passport Number should numeric.',
            'phone.required' => 'Phone Number can not be empty.',
            'prefix.required' => 'Prefix Number can not be empty.',
            'email.unique' => 'The email has already been taken.',
            'pid.unique' => 'ID/Passport has already been taken.',
            'password.required' => 'Password is required.',
            'roles.required' => 'Please Select Role.',
        ];
    }
}
