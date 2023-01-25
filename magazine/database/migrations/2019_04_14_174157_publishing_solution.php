<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class PublishingSolution extends Migration
{
    public function up()
    {
        Schema::table("publish_contents", function (Blueprint  $blueprint){
            $blueprint->dropForeign("publish_contents_publish_id_foreign");
        });
        Schema::drop("publish_contents");
        Schema::table("publishes", function (Blueprint $blueprint){
            $blueprint->longText("content");
        });
    }
    public function down()
    {
    }
}
