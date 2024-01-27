<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FacultySemester extends Model
{
    protected $table = 'faculty_semesters';
    protected $fillable = [
        "faculty_id",
        'semester_id',
        'first_deadline',
        'second_deadline',
        'description',
    ];
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
    public function article()
    {
        return $this->hasMany(Article::class);
    }
    public function faculty_semester_coordinator()
    {
        return $this->hasMany(FacultySemesterCoordinator::class);
    }
    public function faculty_semester_student()
    {
        return $this->hasMany(FacultySemesterStudent::class);
    }
}
