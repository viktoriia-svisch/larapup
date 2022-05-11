<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateForeignKey extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->tinyInteger('gender')->default(1)->comment('1 as male, 2 as female');
        });
        Schema::table('coordinators', function (Blueprint $table) {
            $table->tinyInteger('gender')->default(1)->comment('1 as male, 2 as female');
        });
        Schema::table('guests', function (Blueprint $table) {
            $table->unsignedInteger('faculty_id');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });
        Schema::table('publish_contents', function (Blueprint $table) {
            $table->unsignedInteger('publish_id')->change();
            $table->foreign('publish_id')->references('id')->on('publishes');
        });
        Schema::table('publishes', function (Blueprint $table) {
            $table->unsignedInteger('coordinator_id')->change();
            $table->unsignedInteger('article_id')->change();
            $table->foreign('coordinator_id')->references('id')->on('coordinators');
            $table->foreign('article_id')->references('id')->on('articles');
        });
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedInteger('student_id')->change();
            $table->unsignedInteger('faculty_id')->change();
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });
        Schema::table('article_files', function (Blueprint $table) {
            $table->unsignedInteger('article_id')->change();
            $table->foreign('article_id')->references('id')->on('articles');
        });
        Schema::table('faculty_students', function (Blueprint $table) {
            $table->unsignedInteger('student_id')->change();
            $table->unsignedInteger('faculty_id')->change();
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });
        Schema::table('faculty_coordinators', function (Blueprint $table) {
            $table->unsignedInteger('coordinator_id')->change();
            $table->unsignedInteger('faculty_id')->change();
            $table->foreign('coordinator_id')->references('id')->on('coordinators');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });
    }
    public function down()
    {
        Schema::dropIfExists('foreign_key');
    }
}
