<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreatePublishesTable extends Migration
{
    public function up()
    {
        Schema::create('publishes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coordinator_id');
            $table->integer('article_id');
            $table->string('title');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('publishes');
    }
}
