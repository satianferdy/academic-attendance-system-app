<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:255',
            'edit_notes' => 'nullable|string|max:500',
            'hours_present' => 'required|integer|min:0',
            'hours_absent' => 'required|integer|min:0',
            'hours_permitted' => 'required|integer|min:0',
            'hours_sick' => 'required|integer|min:0',
        ];
    }
}
