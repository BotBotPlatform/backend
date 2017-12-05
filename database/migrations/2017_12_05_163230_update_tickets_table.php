<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::table('tickets', function (Blueprint $table) {
           $table->string('messenger_userid');
           $table->string('name');
         });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::table('tickets', function (Blueprint $table) {
           $table->dropColumn('messenger_userid');
           $table->dropColumn('name');
         });
     }
}
