<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PublishContent extends Model
{
    public function publish(){
        return $this->belongsTo(Publish::class);
    }
}
