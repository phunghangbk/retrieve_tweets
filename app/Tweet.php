<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    protected $table = 'tweets_table';
    protected $primaryKey = 'id';
    protected $fillable = ['tweet_id', 'text', 'tweet_created_at', 'retweet_count', 'hashtags', 'created_at', 'updated_at'];
}
