<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ArticleFile extends Model
{
    public function article(){
        return $this->belongsTo(Article::class);
    }
}
