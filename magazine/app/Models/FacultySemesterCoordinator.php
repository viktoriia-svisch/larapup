<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FacultySemesterCoordinator extends Model
{
    public function faculty_semester()
    {
        return $this->belongsTo(FacultySemester::class);
    }
    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }
}
