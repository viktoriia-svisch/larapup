<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CommentCoordinator extends Model
{
    protected $fillable = [
        "article_id",
        "coordinator_id",
        "content"
    ];
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
    public function coordinator()
    {
        return $this->belongsTo(Coordinator::class);
    }
}
