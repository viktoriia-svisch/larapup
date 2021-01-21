<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateFacultyCoordinatorsTable extends Migration
{
    public function up()
    {
        Schema::create('faculty_coordinators', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coordinator_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('faculty_coordinators');
    }
}
