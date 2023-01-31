<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateTypeUploadFile extends Migration
{
    public function up()
    {
        Schema::table("article_files", function (Blueprint $table){
            $table->integer("type")->after("file_path")->default(0);
        });
        Schema::table("articles",  function (Blueprint $table){
            $table->integer("grade")->after("status")->default(5);
            $table->tinyInteger("grade_status")->default(0)->after("grade");
        });
    }
    public function down()
    {
    }
}
