<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['student_id', 'class_id', 'date', 'status', 'remarks'];

    public function student() {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function classes() {
        return $this->belongsTo(Classes::class, 'class_id', 'class_id'); 
    }
}
