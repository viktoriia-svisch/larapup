<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateFacultySemester extends Migration
{
    public function up()
    {
        Schema::table('faculty_semesters', function (Blueprint $table){
        	$table->dateTime('first_final_deadline')->nullable();
        	$table->dateTime('second_final_deadline')->nullable();
		});
    }
    public function down()
    {
    }
}
