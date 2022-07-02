<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateCommentStudentsTable extends Migration
{
    public function up()
    {
        Schema::create('comment_students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('faculty_semester_id');
            $table->unsignedInteger('student_id');
            $table->mediumText('content');
            $table->mediumText('image_path');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('faculty_semester_id')->references('id')->on('faculty_semesters');
            $table->foreign('student_id')->references('id')->on('students');
        });
    }
    public function down()
    {
        Schema::dropIfExists('comment_students');
    }
}
