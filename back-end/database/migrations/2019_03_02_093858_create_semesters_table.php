<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateSemestersTable extends Migration
{
    public function up()
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('falcutyid');
            $table->string('name');
            $table->dateTime('startdate');
            $table->dateTime('enddate');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('semesters');
    }
}
