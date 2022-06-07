<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FacultyCoordinator extends Model
{
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }
}
