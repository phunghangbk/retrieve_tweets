<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use Log;
use Exception;
use App\Tweet;
class SaveTweets extends Controller
{
    const ERROR_MESSAGE = 'データー格納失敗しました。';
    const SUCCESS_MESSAGE = 'データー格納成功しました。';
    const ERROR_STATUS = 'error';
    const SUCCESS_STATUS = 'success';
    public function savetweets(Request $request)
    {
        try {
            $tweet = new Tweet();
            \Log::info(count(json_decode($request->tweets, true)));
            $records = $this->makeRecord(json_decode($request->tweets, true));
            if (count($records) > 0) {
                if (! $tweet->insert($records)) {
                    return response([
                        'status' => self::ERROR_STATUS,
                        'message' => self::ERROR_MESSAGE
                    ]);
                }
            }

            return response([
                'status' => self::SUCCESS_STATUS,
                'message' => self::SUCCESS_MESSAGE
            ]);
        } catch (Exception $e) {
            \Log::info($e);
            return response([
                'status' => self::ERROR_STATUS,
                'message' => self::ERROR_MESSAGE
            ]);
        }
    }

    public function deleteTweets(Request $request)
    {
        $date = $request->date;
        if (! empty($date)) {
            $d = new DateTime($date);
        } else {
            $d = new DateTime();
            $d->sub(new \DateInterval('P2D'));
        }
        $d = $d->format('Y-m-d');

        $tweet = new Tweet();
        try {
            if (! $tweet->whereDate('created_at', $d)->delete()) {
                \Log::info('Cannot delete data');
            } else {
                \Log::info('Delete data success!!!');
            }
        } catch (Exception $e) {
            \Log::info($e);
        }
    }

    private function tweetID($tweet) 
    {
        return ! empty($tweet['id']) ? $tweet['id'] : '';
    }

    private function tweetIDStr($tweet) {
        return ! empty($tweet['id_str']) ? $tweet['id_str'] : '';
    }

    private function userID($tweet) {
        return ! empty($tweet['user']['id']) ? $tweet['user']['id'] : '';
    }

    private function userName($tweet) {
        return ! empty($tweet['user']['screen_name']) ? $tweet['user']['screen_name'] : '';
    }

    private function tweetCreateAt($tweet)
    {
        $timeUTC = new DateTime($tweet['created_at'], new \DateTimeZone('UTC'));
        $timeUTC->setTimezone(new \DateTimeZone('Asia/Tokyo'));
        $timeJST = $timeUTC->format('Y-m-d H:i:s');
        return $timeJST;
    }

    private function tweetText($tweet)
    {
        if (! empty($tweet['full_text'])) {
            return $tweet['full_text'];
        }

        if (! empty($tweet['text'])) {
            return $tweet['text'];
        }

        return '';
    }

    private function retweetCount($tweet)
    {
        return ! empty($tweet['retweet_count']) ? $tweet['retweet_count'] : 0;
    }

    private function favoriteCount($tweet)
    {
        return ! empty($tweet['favorite_count']) ? $tweet['favorite_count'] : 0;
    }

    private function replyCount($tweet)
    {
        return ! empty($tweet['reply_count']) ? $tweet['reply_count'] : 0;
    }

    private function tweetURL($tweet)
    {
        return 'https://twitter.com/' . $this->userName($tweet) . '/status/' . $this->tweetIDStr($tweet);
    }

    private function hashtags($tweet)
    {
        $result = '';
        if (! empty($tweet['entities']['hashtags']) && is_array($tweet['entities']['hashtags'])) {
            $hashtags = $tweet['entities']['hashtags'];
            for ($i = 0; $i < count($hashtags); $i++) {
                $result .= '#' . $hashtags[$i]['text'] . ' ';
            }
        }

        return $result;
    }

    private function makeRecord($tweets)
    {
        if (! is_array($tweets) || count($tweets) == 0) {
            return [];
        }
        $records = [];
        $now = new DateTime();
        for($i = 0; $i < count($tweets); $i++) {
            $records[] = [
                'tweet_id' => $this->tweetID($tweets[$i]), 
                'text' => $this->tweetText($tweets[$i]),
                'tweet_id_str' => $this->tweetIDStr($tweets[$i]),
                'user_id' => $this->userID($tweets[$i]),
                'user_name' => $this->userName($tweets[$i]),
                'tweet_created_at' => $this->tweetCreateAt($tweets[$i]),
                'retweet_count' => $this->retweetCount($tweets[$i]),
                'favorite_count' => $this->favoriteCount($tweets[$i]),
                'reply_count' => $this->replyCount($tweets[$i]),
                'tweet_url' => $this->tweetURL($tweets[$i]),
                'hashtags' => $this->hashtags($tweets[$i]),
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s')
            ];
        }

        return $records;
    }
}
