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
            'developer_id' => 'nullable|exists:developers,id',
            'investor_ids' => 'nullable',
            'gallery' => 'nullable|array',
            // Allow both file uploads and existing image URLs
            'gallery.*' => 'nullable',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('gallery') && is_array($this->gallery)) {
                foreach ($this->gallery as $index => $item) {
                    // If it's a file upload, validate as image
                    if ($this->hasFile("gallery.{$index}")) {
                        $file = $this->file("gallery.{$index}");
                        if (!$file->isValid()) {
                            $validator->errors()->add("gallery.{$index}", 'The uploaded file is not valid.');
                            continue;
                        }
                        
                        // Check if it's an image
                        $mimeType = $file->getMimeType();
                        if (strpos($mimeType, 'image/') !== 0) {
                            $validator->errors()->add("gallery.{$index}", 'Each gallery file must be an image.');
                            continue;
                        }
                        
                        // Check file size (5MB = 5120KB)
                        if ($file->getSize() > 5120 * 1024) {
                            $validator->errors()->add("gallery.{$index}", 'Each gallery image may not be greater than 5MB.');
                        }
                    }
                    // If it's a string (existing image URL), validate URL format
                    elseif (is_string($item) && !empty($item)) {
                        if (!filter_var($item, FILTER_VALIDATE_URL) && strpos($item, '/') !== 0) {
                            $validator->errors()->add("gallery.{$index}", 'Invalid image URL format.');
                        }
                    }
                }
            }
        });
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
            'developer_id.exists' => 'The selected developer is invalid.',
        ];
    }
}
