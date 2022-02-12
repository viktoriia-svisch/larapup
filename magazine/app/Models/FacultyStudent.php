<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FacultyStudent extends Model
{
    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
