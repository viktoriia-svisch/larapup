<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreatePublishContentsTable extends Migration
{
    public function up()
    {
        Schema::create('publish_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('publish_id');
            $table->integer('content');
            $table->string('image_path');
            $table->mediumText('image_description');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('publish_contents');
    }
}
