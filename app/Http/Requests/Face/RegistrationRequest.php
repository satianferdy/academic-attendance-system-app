<?php

namespace App\Http\Requests\Face;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'images' => 'required|array|min:5|max:5',
            'images.*' => 'required|image|max:5120',
            'redirect_url' => 'nullable|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'images.required' => 'Please provide face images for registration.',
            'images.min' => 'You must provide exactly 5 face images.',
            'images.max' => 'You must provide exactly 5 face images.',
            'images.*.image' => 'One or more files are not valid images.',
            'images.*.max' => 'One or more images exceed the maximum file size (5MB).',
        ];
    }
}
