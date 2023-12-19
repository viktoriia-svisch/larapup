<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateSemesterSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('month')->nullable()->default(3);
            $table->string('prefix')->nullable();
            $table->integer("deadline_day_span")->nullable()->default(30);
            $table->string("faculty_prefix")->nullable();
            $table->timestamps();
        });
        Schema::table("articles", function (Blueprint $blueprint){
            $blueprint->removeColumn("title");
            $blueprint->removeColumn("description");
            $blueprint->removeColumn("cover");
            $blueprint->removeColumn("grade_status");
        });
    }
    public function down()
    {
        Schema::dropIfExists('semester_settings');
    }
}
