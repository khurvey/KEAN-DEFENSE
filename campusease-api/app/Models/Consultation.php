<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'student_id',
        'faculty_id',
        'faculty_subject_id',
        'concern',
        'details',
        'appointment_date',
        'appointment_time',
        'status',
        'remarks',
    ];
}