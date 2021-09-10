<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateFacultyStudentsTable extends Migration
{
    public function up()
    {
        Schema::create('faculty_students', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('student_id');
            $table->integer('faculty_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('faculty_students');
    }
}
