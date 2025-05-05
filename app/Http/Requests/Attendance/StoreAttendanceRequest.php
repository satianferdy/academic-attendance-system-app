<?php

namespace App\Http\Requests\Attendance;

use App\Models\ClassSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

     public function authorize(): bool
     {
         $classSchedule = ClassSchedule::find($this->class_id);

         // Using Auth facade instead of auth() helper
         if (!Auth::check()) {
             return false;
         }

         $user = Auth::user();

         // Check if user has a lecturer relationship
         if (!$user || !isset($user->lecturer)) {
             return false;
         }

         return $classSchedule && $classSchedule->lecturer_id == $user->lecturer->id;
     }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:class_schedules,id',
            'date' => 'required|date|after_or_equal:today',
            'week' => 'required|integer|min:1|max:24',
            'meetings' => 'required|integer|min:1|max:7',
        ];
    }
}
