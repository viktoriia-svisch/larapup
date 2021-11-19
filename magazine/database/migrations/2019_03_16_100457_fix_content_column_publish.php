<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class FixContentColumnPublish extends Migration
{
    public function up()
    {
        Schema::table('publish_contents', function (Blueprint $table){
            $table->mediumText('content')->change();
            $table->text('image_description')->change();
        });
    }
    public function down()
    {
    }
}
