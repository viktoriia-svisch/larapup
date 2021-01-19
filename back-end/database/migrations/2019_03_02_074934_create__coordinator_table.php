<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateCoordinatorTable extends Migration
{
    public function up()
    {
        Schema::create('coordinator', function (Blueprint $table) {
            $table->increments('id');
            $table->string('UserName');
            $table->string('Password');
            $table->string('Name');
            $table->integer('Type');
            $table->integer('Status');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('_coordinator');
    }
}
