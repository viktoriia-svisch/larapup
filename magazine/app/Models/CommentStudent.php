<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CommentStudent extends Model
{
    public function faculty_semester_student(){
        return $this->belongsTo(FacultySemesterStudent::class);
    }
    public function student(){
        return $this->belongsTo(Student::class);
    }
}
