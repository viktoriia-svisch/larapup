<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreatePublishImage extends Migration
{
    public function up()
    {
        Schema::create('publish_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("publish_id");
            $table->unsignedInteger("article_id");
            $table->text("image_path");
            $table->tinyInteger("image_ext");
            $table->mediumText("description");
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('publish_id')->references('id')->on('publishes');
            $table->foreign('article_id')->references('id')->on('articles');
        });
        Schema::table("publish_contents", function (Blueprint $blueprint){
            $blueprint->dropColumn("image_path");
            $blueprint->dropColumn("image_description");
        });
    }
    public function down()
    {
        Schema::dropIfExists('publish_image');
    }
}
