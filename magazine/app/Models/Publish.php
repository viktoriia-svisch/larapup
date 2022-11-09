<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Publish extends Model
{
    public function article(){
        return $this->belongsTo(Article::class);
    }
    public function coordinator(){
        return $this->belongsTo(Coordinator::class);
    }
    public function publish_content(){
        return $this->hasMany(PublishContent::class);
    }
}
