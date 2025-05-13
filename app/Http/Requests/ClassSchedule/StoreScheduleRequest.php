<?php

namespace App\Http\Requests\ClassSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\ClassSchedule::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'lecturer_id' => 'required|exists:lecturers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'semester_id' => 'required|exists:semesters,id',
            'study_program_id' => 'required|exists:study_programs,id',
            'room' => 'required|string|max:50',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'time_slots' => 'required|array|min:1',
            'semester' => 'required|string|max:20',
            'total_weeks' => 'required|integer|min:1|max:52',
            'meetings_per_week' => 'required|integer|min:1|max:7',
        ];
    }
}
