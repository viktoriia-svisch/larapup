<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FacultySemesterCoordinator extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'faculty_semester_id',
        'coordinator_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function faculty_semester()
    {
        return $this->belongsTo(FacultySemester::class);
    }
    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }
}
