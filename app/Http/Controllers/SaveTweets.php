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
            $records = $this->makeRecord($request->tweets);
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

    private function tweetID($tweet) 
    {
        return ! empty($tweet['id']) ? $tweet['id'] : '';
    }

    private function tweetCreateAt($tweet)
    {
        \Log::info($tweet);
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

    private function hashtags($tweet)
    {
        $result = '';
        if (! empty($tweet['entities']['hashtags']) && is_array($tweet['entities']['hashtags'])) {
            \Log::info($tweet['entities']['hashtags']);
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
                'tweet_created_at' => $this->tweetCreateAt($tweets[$i]),
                'retweet_count' => $this->retweetCount($tweets[$i]),
                'hashtags' => $this->hashtags($tweets[$i]),
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s')
            ];
        }

        return $records;
    }
}
