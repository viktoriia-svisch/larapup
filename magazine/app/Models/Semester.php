<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Semester extends Model
{
    public $primaryKey = 'id';
    protected $table = 'semesters';
    public function faculty_semester()
    {
        return $this->hasMany(FacultySemester::class);
    }
}
