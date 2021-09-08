<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateStudentsTable extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 90)->unique();
            $table->string('password', 60);
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('status')->default(0)->comment('refer to constant.php');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
