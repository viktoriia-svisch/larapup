<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateSemestersTable extends Migration
{
    public function up()
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->increments('id') -> unsigned();
            $table->string('name');
            $table->string('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('semesters');
    }
}
