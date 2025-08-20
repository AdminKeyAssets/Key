<?php

namespace App\Modules\Admin\Http\Requests\News;

use Illuminate\Foundation\Http\FormRequest;

class SaveNewsRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'in:draft,published',
            'manager_id' => 'nullable|exists:admins,id',
            'investor_ids' => 'nullable',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|file|image|max:5120', // 5MB max per image
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
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'content.required' => 'The content field is required.',
            'status.in' => 'The status must be either draft or published.',
            'manager_id.exists' => 'The selected manager is invalid.',
            'gallery.*.image' => 'Each gallery file must be an image.',
            'gallery.*.max' => 'Each gallery image may not be greater than 5MB.',
        ];
    }
}
