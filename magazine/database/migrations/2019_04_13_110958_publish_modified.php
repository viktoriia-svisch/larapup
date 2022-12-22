<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class PublishModified extends Migration
{
    public function up()
    {
        Schema::table("publish_contents", function (Blueprint $blueprint){
            $blueprint->mediumText("content")->nullable()->change();
            $blueprint->string("image_path")->nullable()->change();
            $blueprint->mediumText("image_description")->nullable()->change();
        });
    }
    public function down()
    {
    }
}
