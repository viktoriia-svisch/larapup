<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FacultySemester extends Model
{
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
