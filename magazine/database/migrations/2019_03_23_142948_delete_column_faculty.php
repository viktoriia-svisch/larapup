<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class DeleteColumnFaculty extends Migration
{
    public function up()
    {
        Schema::table('faculties',function (Blueprint $table){
            $table->dropColumn('first_deadline');
            $table->dropColumn('second_deadline');
        });
    }
    public function down()
    {
    }
}
