<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Faculty extends Model
{
    protected $table = 'faculties';
    public function faculty_semester()
    {
        return $this->hasMany(FacultySemester::class);
    }
    public function guest()
    {
        return $this->hasMany(Guest::class);
    }
}
