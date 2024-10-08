<?php

namespace App\Modules\Admin\Http\Requests\Investor;

use Illuminate\Foundation\Http\FormRequest;

class NotifyInvestorRequest extends FormRequest
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
            'body'  => 'required',
            'investor_id'  => 'required',
        ];
    }

    public function messages(){
        return [
            'body.required' => 'Email Body can not be empty.',
            'investor_id.required' => 'Investor is required.',
        ];
    }
}
