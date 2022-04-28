<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Faculty extends Model
{
    public function faculty_semester()
    {
        return $this->hasMany(Semester::class);
    }
    public function guest()
    {
        return $this->hasMany(Guest::class);
    }
}
