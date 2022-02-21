<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class AddAvatarColumn extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('avatar_path')->nullable();
            $table->date('dateOfBirth')->nullable();
        });
        Schema::table('coordinators', function (Blueprint $table) {
            $table->string('avatar_path')->nullable();
            $table->date('dateOfBirth')->nullable();
        });
    }
    public function down()
    {
    }
}
