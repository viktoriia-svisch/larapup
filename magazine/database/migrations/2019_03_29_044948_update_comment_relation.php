<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateCommentRelation extends Migration
{
    public function up()
    {
        Schema::table('comment_coordinators', function (Blueprint $table){
            $table->dropForeign('comment_coordinators_faculty_semester_id_foreign'); 
            $table->dropColumn('faculty_semester_id');
            $table->unsignedInteger('article_id')->after('id');
            $table->foreign('article_id')->references('id')->on('articles');
        });
        Schema::table('comment_students', function (Blueprint $table){
            $table->dropForeign('comment_students_faculty_semester_id_foreign'); 
            $table->dropColumn('faculty_semester_id');
            $table->unsignedInteger('article_id')->after('id');
            $table->foreign('article_id')->references('id')->on('articles');
        });
    }
    public function down()
    {
    }
}
