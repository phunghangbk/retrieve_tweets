<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTweetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tweets_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tweet_id');
            $table->string('tweet_id_str');
            $table->string('user_id');
            $table->string('user_name');
            $table->text('text');
            $table->string('tweet_created_at');
            $table->integer('retweet_count');
            $table->integer('favorite_count');
            $table->integer('reply_count');
            $table->string('tweet_url');
            $table->text('hashtags');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tweets_table');
    }
}
