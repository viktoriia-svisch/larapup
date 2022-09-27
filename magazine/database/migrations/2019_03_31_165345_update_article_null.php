<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateArticleNull extends Migration
{
    public function up()
    {
        Schema::table("articles", function (Blueprint $table){
            $table->string("title")->nullable()->change();
            $table->longText("description")->nullable()->change();
            $table->dropColumn("avatar");
        });
    }
    public function down()
    {
    }
}
