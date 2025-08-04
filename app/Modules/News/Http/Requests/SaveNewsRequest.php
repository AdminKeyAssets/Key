<?php

namespace App\Modules\News\Http\Requests;

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
     * @return void
     */
    protected function prepareForValidation()
    {
        $data = $this->all();

        array_walk_recursive($data, function (&$value) {
            if ($value === 'null') {
                $value = null;
            }
            if ($value === 'false') {
                $value = false;
            }
            if ($value === 'true') {
                $value = true;
            }
        });

        $this->merge($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'status' => 'required|in:published,draft,archived',
            'admin_id' => 'nullable|exists:admins,id',
            'investor_ids' => 'nullable|array',
            'investor_ids.*' => 'exists:investors,id',
        ];

        // Image validation only when file is present
        if ($this->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    /**
     * Get custom validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'The news title is required.',
            'title.string' => 'The news title must be a string.',
            'title.max' => 'The news title may not be greater than 255 characters.',
            'content.required' => 'The news content is required.',
            'content.string' => 'The news content must be a string.',
            'content.max' => 'The news content may not be greater than 5000 characters.',
            'status.required' => 'The news status is required.',
            'status.in' => 'The status must be one of: published, draft, archived.',
            'admin_id.exists' => 'The selected manager does not exist.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
            'investor_ids.array' => 'The investors field must be an array.',
            'investor_ids.*.exists' => 'One or more selected investors do not exist.',
        ];
    }
}
