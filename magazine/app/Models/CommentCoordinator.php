<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CommentCoordinator extends Model
{
    public function faculty_semester_coordinator(){
        return $this->belongsTo(FacultySemesterCoordinator::class);
    }
    public function coordinator(){
        return $this->belongsTo(Coordinator::class);
    }
}
