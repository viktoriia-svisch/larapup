<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class UpdateTypeTable extends Migration
{
    public function up()
    {
        Schema::table('articles',function (Blueprint $table){
            $table->longText('description')->change();
        });
    }
    public function down()
    {
    }
}
