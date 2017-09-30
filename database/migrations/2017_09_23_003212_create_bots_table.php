<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bots', function (Blueprint $table) {
          $table->increments('id');
          $table->uuid('uuid');

          $table->integer('port')->nullable();
          $table->string('deploy_status')->default('offline');
          $table->timestamp('heartbeat')->nullable();

          $table->boolean('bot_enabled')->default(false);
          $table->boolean('shopify_enabled')->default(false);

          $table->integer('user_id')->unsigned();
          $table->foreign('user_id')->references('id')->on('users')
              ->onUpdate('cascade')->onDelete('cascade');

          $table->softDeletes();
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
        Schema::dropIfExists('bots');
    }
}
