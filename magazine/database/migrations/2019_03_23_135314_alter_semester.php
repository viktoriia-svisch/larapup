<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class AlterSemester extends Migration
{
    public function up()
    {
        Schema::table('semesters', function (Blueprint $table){
            $table->dropForeign('semesters_faculty_id_foreign');
            $table->dropColumn('faculty_id');
        });
    }
    public function down()
    {
    }
}
