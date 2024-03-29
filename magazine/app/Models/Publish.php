<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Publish extends Model
{
    protected $fillable = [
        "coordinator_id",
        "title",
        "article_id",
        "created_at"
    ];
    public function article(){
        return $this->belongsTo(Article::class);
    }
    public function coordinator(){
        return $this->belongsTo(Coordinator::class);
    }
    public function publish_image(){
        return $this->hasMany(PublishImage::class);
    }
}
