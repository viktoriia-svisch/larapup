<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateArticleFilesTable extends Migration
{
    public function up()
    {
        Schema::create('article_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id');
            $table->string('title');
            $table->string('file_path');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down()
    {
        Schema::dropIfExists('article_files');
    }
}
