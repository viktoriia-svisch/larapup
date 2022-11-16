<?php
namespace App\Http\Controllers\Admin;
class FacultySemesterCoordinator
{
    public function faculty_semester()
        {
            return $this->belongsTo(FacultySemester::class);
        }
        public function coordinator()
        {
            return $this->belongsTo(Coordinator::class);
        }
        public function comment_student(){
            return $this->hasMany(CommentStudent::class);
        }
}
