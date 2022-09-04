<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class FixFacultySemester extends Migration
{
    public function up()
    {
        Schema::table('faculty_semesters', function (Blueprint $table){
            $table->dropColumn('first_final_deadline');
            $table->dropColumn('second_final_deadline');
        });
    }
    public function down()
    {
    }
}
