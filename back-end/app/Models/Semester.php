<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    public function faculty()
    {
        return $this->hasMany(Faculty::class);
    }
}
