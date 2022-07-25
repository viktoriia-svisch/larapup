<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class ChanegStructure extends Migration
{
    public function up()
    {
        Schema::table('faculties',function (Blueprint $table){
            $table->dropForeign('faculties_semester_id_foreign');
            $table->dropColumn('semester_id');
        });
        Schema::table('semesters',function (Blueprint $table){
            $table->unsignedInteger('faculty_id')->after('id');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });
        Schema::table('faculty_coordinators',function (Blueprint $table){
            $table->unsignedInteger('semester_id')->after('coordinator_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });
        Schema::table('faculty_students',function (Blueprint $table){
            $table->unsignedInteger('semester_id')->after('student_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });
    }
    public function down()
    {
    }
}
