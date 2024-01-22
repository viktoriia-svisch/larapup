<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FacultySemesterStudent extends Model
{
    protected $fillable = [
        'faculty_semester_id',
        'student_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function faculty_semester()
    {
        return $this->belongsTo(FacultySemester::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function comment_student()
    {
        return $this->hasMany(CommentStudent::class);
    }
}
