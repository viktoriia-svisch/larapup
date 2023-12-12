<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Semester extends Model
{
    use SoftDeletes;
    public $primaryKey = 'id';
    protected $table = 'semesters';
    public function faculty_semester()
    {
        return $this->hasMany(FacultySemester::class);
    }
}
