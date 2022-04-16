<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateFacultySemestersTable extends Migration
{
    public function up()
    {
        Schema::create('faculty_semesters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('semester_id');
            $table->unsignedInteger('faculty_id');
            $table->dateTime('first_deadline');
            $table->dateTime('second_deadline');
            $table->longText('description');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });
        Schema::create('faculty_semester_coordinators', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('faculty_semester_id');
            $table->unsignedInteger('coordinator_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('faculty_semester_id')->references('id')->on('faculty_semesters');
            $table->foreign('coordinator_id')->references('id')->on('coordinators');
        });
        Schema::create('faculty_semester_students', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('faculty_semester_id');
            $table->unsignedInteger('student_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('faculty_semester_id')->references('id')->on('faculty_semesters');
            $table->foreign('student_id')->references('id')->on('students');
        });
        Schema::table('articles', function (Blueprint $table){
            $table->dropForeign('articles_faculty_id_foreign');
            $table->dropColumn('faculty_id');
            $table->unsignedInteger('faculty_semester_id')->after('student_id');
            $table->foreign('faculty_semester_id')->references('id')->on('faculty_semesters');
        });
        Schema::table('faculty_coordinators',function (Blueprint $table) {
            $table->dropForeign('faculty_coordinators_faculty_id_foreign');
            $table->dropForeign('faculty_coordinators_semester_id_foreign');
            $table->dropForeign('faculty_coordinators_coordinator_id_foreign');
        });
        Schema::table('faculty_students',function (Blueprint $table) {
            $table->dropForeign('faculty_students_faculty_id_foreign');
            $table->dropForeign('faculty_students_semester_id_foreign');
            $table->dropForeign('faculty_students_student_id_foreign');
        });
        Schema::dropIfExists('faculty_coordinators');
        Schema::dropIfExists('faculty_students');
    }
    public function down()
    {
        Schema::dropIfExists('faculty_semesters');
    }
}
