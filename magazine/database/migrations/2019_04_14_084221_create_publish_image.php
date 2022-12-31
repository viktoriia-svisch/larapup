<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreatePublishImage extends Migration
{
    public function up()
    {
        Schema::create('publish_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("publish_id");
            $table->unsignedInteger("article_id");
            $table->
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('publish_image');
    }
}
