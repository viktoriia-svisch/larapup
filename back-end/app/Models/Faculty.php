<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    public function faculty_coordinator()
    {
        return $this->hasMany(FacultyCoordinator::class);
    }

    public function faculty_student()
    {
        return $this->hasMany(FacultyStudent::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
