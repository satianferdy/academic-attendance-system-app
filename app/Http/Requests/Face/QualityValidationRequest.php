<?php

namespace App\Http\Requests\Face;

use Illuminate\Foundation\Http\FormRequest;

class QualityValidationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'image' => 'required|image|max:5120',
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
            'image.required' => 'Please provide a face image for quality validation.',
            'image.image' => 'The file is not a valid image.',
            'image.max' => 'The image exceeds the maximum file size (5MB).',
        ];
    }
}
