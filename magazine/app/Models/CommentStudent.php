<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CommentStudent extends Model
{
    public function article(){
        return $this->belongsTo(Article::class);
    }
    public function student(){
        return $this->belongsTo(Student::class);
    }
}
