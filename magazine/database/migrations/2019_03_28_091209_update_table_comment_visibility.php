<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateTableCommentVisibility extends Migration
{
    public function up()
    {
        Schema::table('comment_students', function (Blueprint $table){
        	$table->tinyInteger('notified')->after('image_path')->default(0);
		});
		Schema::table('comment_coordinators', function (Blueprint $table){
			$table->tinyInteger('notified')->after('image_path')->default(0);
		});
    }
    public function down()
    {
    }
}
