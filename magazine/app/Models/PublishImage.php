<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PublishImage extends Model
{
    protected $fillable = [
        "publish_id",
        "image_path",
        "image_ext",
        "description"
    ];
    public function publish(){
        return $this->belongsTo(Publish::class);
    }
}
