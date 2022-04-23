<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Article extends Model
{
    protected $table = 'articles';
    public function article_file(){
        return $this->hasMany(ArticleFile::class);
    }
    public function student(){
        return $this->belongsTo(Student::class);
    }
    public function faculty_semester(){
        return $this->belongsTo(FacultySemester::class);
    }
}
