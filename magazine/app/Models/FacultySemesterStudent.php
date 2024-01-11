<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    public function comment_student(){
        return $this->hasMany(CommentStudent::class);
    }
}
