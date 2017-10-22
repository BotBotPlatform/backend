<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('feedback_category', function (Blueprint $table) {
           $table->increments('id');
           $table->string('name');

           $table->integer('bot_id')->unsigned();
           $table->foreign('bot_id')->references('id')->on('bots')
               ->onUpdate('cascade')->onDelete('cascade');

           $table->timestamps();
         });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::dropIfExists('feedback_category');
     }
}
