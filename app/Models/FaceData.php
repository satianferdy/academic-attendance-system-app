<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FaceData extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'face_embedding',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'face_embedding' => 'array', // Assuming face_embedding is stored as a JSON array
        'image_path' => 'array', // Assuming image_path is a string
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
