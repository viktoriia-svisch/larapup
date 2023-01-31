<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class UpdateImagePath extends Migration
{
    public function up()
    {
        Schema::table("comment_students", function (Blueprint $table) {
            $table->mediumText("image_path")->nullable()->change();
        });
    }
    public function down()
    {
    }
}
