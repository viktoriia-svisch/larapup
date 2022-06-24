<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateCommentCoordinatorsTable extends Migration
{
    public function up()
    {
        Schema::create('comment_coordinators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('faculty_semester_id');
            $table->unsignedInteger('coordinator_id');
            $table->mediumText('content');
            $table->mediumText('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('faculty_semester_id')->references('id')->on('faculty_semesters');
            $table->foreign('coordinator_id')->references('id')->on('coordinators');
        });
    }
    public function down()
    {
        Schema::dropIfExists('comment_coordinators');
    }
}
