<?php

namespace App\Modules\Admin\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDeveloperRequest extends FormRequest
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
        $passwordRequired = empty($this->request->all()['id']) ? 'required' : '';

        return [
            'name' => 'required',
            'id_code' => ['required', !empty($this->request->all()['id']) ? Rule::unique('developers', 'id_code')->ignore($this->request->all()['id']) : 'unique:developers,id_code'],
            'representative' => 'required',
            'tel' => 'required',
            'representative_position' => 'required',
            'username' => ['required', !empty($this->request->all()['id']) ? Rule::unique('developers', 'username')->ignore($this->request->all()['id']) : 'unique:developers,username'],
            'password' => [$passwordRequired],
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stamp' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'signature' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_agreement' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Developer Name cannot be empty.',
            'id_code.required' => 'ID Code cannot be empty.',
            'id_code.unique' => 'This ID Code has already been taken.',
            'representative.required' => 'Representative name cannot be empty.',
            'tel.required' => 'Telephone number cannot be empty.',
            'representative_position.required' => 'Representative Position cannot be empty.',
            'username.required' => 'Username cannot be empty.',
            'username.unique' => 'This username has already been taken.',
            'password.required' => 'Password cannot be empty for new developers.',
            'logo.mimes' => 'Logo must be an image (jpeg, png, jpg, gif, svg).',
            'logo.max' => 'Logo size cannot exceed 2MB.',
            'stamp.mimes' => 'Stamp must be an image (jpeg, png, jpg, gif, svg).',
            'stamp.max' => 'Stamp size cannot exceed 2MB.',
            'signature.mimes' => 'Signature must be an image (jpeg, png, jpg, gif, svg).',
            'signature.max' => 'Signature size cannot exceed 2MB.',
            'service_agreement.mimes' => 'Service Agreement must be a document (pdf, doc, docx).',
            'service_agreement.max' => 'Service Agreement size cannot exceed 5MB.',
        ];
    }
}
