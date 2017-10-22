<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('feedback', function (Blueprint $table) {
           $table->increments('id');
           $table->text('message');

           $table->integer('feedback_category_id')->unsigned();
           $table->foreign('feedback_category_id')->references('id')->on('feedback_category')
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
         Schema::dropIfExists('feedback');
     }
}
