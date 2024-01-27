<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CleaingDatabaseFinal extends Migration
{
    public function up()
    {
        Schema::table("articles", function (Blueprint $table){
            $table->removeColumn("title");
            $table->removeColumn("description");
            $table->removeColumn("cover");
            $table->removeColumn("grade_status");
        });
        Schema::table("faculties", function (Blueprint $table){
            $table->removeColumn("description");
        });
    }
    public function down()
    {
    }
}
