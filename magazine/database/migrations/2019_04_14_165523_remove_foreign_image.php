<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class RemoveForeignImage extends Migration
{
    public function up()
    {
        Schema::table('publish_images', function (Blueprint $table) {
            $table->dropForeign('publish_images_article_id_foreign');
            $table->dropColumn("article_id");
        });
    }
    public function down()
    {
    }
}
