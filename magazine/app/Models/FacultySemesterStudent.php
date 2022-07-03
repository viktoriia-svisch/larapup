<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FacultySemesterStudent extends Model
{
    public function faculty_semester()
    {
        return $this->belongsTo(FacultySemester::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
