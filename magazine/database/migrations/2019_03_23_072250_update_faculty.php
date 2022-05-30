<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateFaculty extends Migration
{
    public function up()
    {
        Schema::table('faculties', function (Blueprint $table){
            $table->mediumText('description')->nullable();
        });
    }
    public function down()
    {
    }
}
