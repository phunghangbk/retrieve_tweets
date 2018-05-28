<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    protected $table = 'tweets_table';
    protected $primaryKey = 'id';
    protected $fillable = ['tweet_id', 'tweet_id_str', 'user_id', 'user_name', 'text', 'tweet_created_at', 'retweet_count', 'favorite_count', 'reply_count', 'tweet_url',  'hashtags', 'created_at', 'updated_at'];
}
