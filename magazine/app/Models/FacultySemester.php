<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FacultySemester extends Model
{
    protected $table = 'faculty_semesters';
    public function semester(){
        return $this->belongsTo(Semester::class);
    }
    public function faculty(){
        return $this->belongsTo(Student::class);
    }
    public function article(){
        return $this->hasMany(Article::class);
    }
    public function faculty_semester_coordinator(){
        return $this->hasMany(FacultySemesterCoordinator::class);
    }
    public function faculty_semester_student(){
        return $this->hasMany(FacultySemesterStudent::class);
    }
}
