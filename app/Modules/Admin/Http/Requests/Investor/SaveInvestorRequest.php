<?php

namespace App\Modules\Admin\Http\Requests\Investor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInvestorRequest extends FormRequest
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
            'surname'  => 'required',
            'email' => ['required', !empty($this->request->all()['id']) ? Rule::unique('investors', 'pid')->ignore($this->request->all()['id']) : 'unique:investors,email', 'email'],
            'pid' => ['required', !empty($this->request->all()['id']) ? Rule::unique('investors', 'pid')->ignore($this->request->all()['id']) : 'unique:investors,pid'],
            'phone' => 'required',
            'prefix' => 'required',
            'citizenship' => 'required',
            'address' => 'required',
            'admin_id' => 'required',
            'password'  => [$passwordRequired,
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
            'name.required' => 'Name can not be empty.',
            'surname.required' => 'Surname can not be empty.',
            'email.required' => 'Email can not be empty.',
            'admin_id.required' => 'Please Select manager.',
            'email.email' => 'Missing @ in Email.',
            'pid.required' => 'ID/Passport Number can not be empty.',
            'phone.required' => 'Phone Number can not be empty.',
            'prefix.required' => 'Prefix can not be empty.',
            'citizenship.required' => 'Citizenship can not be empty.',
            'address.required' => 'Address can not be empty.',
            'email.unique' => 'The email has already been taken.',
            'pid.unique' => 'ID/Passport has already been taken.',
        ];
    }
}
