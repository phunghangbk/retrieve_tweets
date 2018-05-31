<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twitter;
use DateTime;
use Validator;
use TwitterAPIExchange;
use Exception;

class RetrieveTweetsController extends Controller
{
    const KEYWORD_ERROR_MESSAGE = 'キーワードを入力して下さい';
    const EMPTY_TIME_ERROR_MESSAGE = '検索したい時間帯を指定してください。';
    const TIME_ERROR_MESSAGE = '終了日付を開始日付より大きく指定して下さい。';
    const DATE_LENGTH = 10;
    const MAX = 20;
    public function getTweets(Request $request)
    {
        $errors = [];
        $startDate = '';
        $endDate = '';
        $startTime = '';
        $endTime = '';

        if (empty($request->keyword)) {
            $errors['keyword'] = self::KEYWORD_ERROR_MESSAGE;
        }

        if (empty($request->start_time) || empty($request->end_time)) {
            $errors['empty_time'] = self::EMPTY_TIME_ERROR_MESSAGE;
        }

        if (! empty($request->start_time)) {
            $startDate = (new DateTime($request->start_time))->format('Y-m-d');
            $startTime = (new DateTime($request->start_time))->format('H:i:s');
        }

        if (! empty($request->end_time)) {
            $endDate = (new DateTime($request->end_time))->format('Y-m-d');
            if (strlen($request->end_time) > self::DATE_LENGTH) {
                $endTime = (new DateTime($request->end_time))->format('H:i:s');
            } else {
                $endTime = '23:59:59';
            }
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            $errors['time'] = self::TIME_ERROR_MESSAGE;
        }

        if (! empty($errors)) {
            return response(['error' => $errors]);
        }

        $twitter = $this->authenticateTwitter(
            config('ttwitter.ACCESS_TOKEN'),
            config('ttwitter.ACCESS_TOKEN_SECRET'),
            config('ttwitter.CONSUMER_KEY'),
            config('ttwitter.CONSUMER_SECRET')
        );
        try {
            $tweets = $this->retrieveAllTweetsWhenAPIStop($twitter, $startDate, $startTime, $endDate, $endTime, $request->keyword);
            \Log::info(count($tweets->statuses));
            $tweets = $this->sort($tweets);
            
            return response([
                'tweets' => $tweets
            ]);
        } catch (Exception $e) {
            \Log::info($e);
            return response($e);
        }
    }

    public function tweets(Request $request)
    {
        $userName = ! empty($request->user_name) ? $request->user_name : '';
        $start = ! empty($request->start) ? $request->start : '';
        $end = ! empty($request->end) ? $request->end : '';
        $keyword = ! empty($request->keyword) ? $request->keyword : '';
        return view('tweets', compact('userName', 'start', 'end', 'keyword'));
    }

    private function changeSearchTimeZone($date = '', $time = '', $timeZone = 'JST')
    {
        $result = '';
        if (! empty($date)) {
            $result .= $date;
            if (! empty($time)) {
                $result .= '_' . $time;
            }
        } else {
            if (! empty($time)) {
                $result .= $time;
            }
        }
        if (! empty($result)) {
            if (! empty($timeZone)) {
                $result .= '_' . $timeZone;
            } else {
                $result .= '_JST';
            }
        }

        return $result;
    }

    /**
     * authenticate twitter dev account 
     * return authenticated twitter object
     * @param  $access_token
     * @param  $access_token_secret
     * @param  $consumer_key
     * @param  $consumer_secret
     * @return [object] $twitter
     */
    private function authenticateTwitter($access_token, $access_token_secret, $consumer_key, $consumer_secret)
    {
        $settings = array(
            'oauth_access_token' => $access_token,
            'oauth_access_token_secret' => $access_token_secret,
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret
        );
        $twitter = new TwitterAPIExchange($settings);
        return $twitter;
    }

    /**
     * get first 100 tweets
     * @param  [object] $twitter
     * @param  [string] $parameters
     * @return [json]   $tweets
     */
    private function retrieveTweets($twitter, $parameters)
    {
        $url = config('ttwitter.SEARCH_URL');
        $unavailableCnt = 0;
        while(1) {
            $tweets = $twitter->setGetfield($parameters)
                ->buildOauth($url, 'GET')
                ->performRequest(); 
            $status_code = $twitter->getHttpStatusCode();
            if (isset($status_code) && $status_code == 503) {
                # 503 : Service Unavailable
                if ($unavailableCnt > 10) {
                    throw new Exception("Twitter API error " . $status_code);
                }
                $unavailableCnt += 1;
                $this->waitUntilReset(time() + 30);
                continue;
            }
            $unavailableCnt = 0;

            if ($status_code == 429) {
                $this->waitUntilReset(time() + 100);
            } else if (isset($status_code) && $status_code != 200) {
                throw new Exception("Twitter API error " . $status_code);
            } else {
                break;
            }
        }
        $tweets = json_decode($tweets);
        return $tweets;
    }

    /**
     * get more than 100 tweets
     * @param  [object] $twitter
     * @param  [string] $parameters 
     * @return [json]   $tweets
     */
    private function retrieveAllTweets($twitter, $parameters)
    {
        $tweets = $this->retrieveTweets($twitter, $parameters);

        $next_max_id = '';
        $next_results_url_params = ! empty($tweets->search_metadata->next_results) ? $tweets->search_metadata->next_results : '';
        while (! empty($next_results_url_params)) {
            $next_max_id = explode('&', explode('max_id=', $next_results_url_params)[1])[0];
            $next_results = $this->retrieveTweets($twitter, $parameters . '&max_id=' . $next_max_id);

            $tweets->statuses = array_merge($tweets->statuses, $next_results->statuses);
            $next_results_url_params = ! empty($next_results->search_metadata->next_results) ? $next_results->search_metadata->next_results : '';
        }

        return $tweets;
    }

    private function retrieveAllTweetsWhenAPIStop($twitter, $startDate, $startTime, $endDate, $endTime, $keyword)
    {
        $tweets = $this->retrieveAllTweets($twitter, $this->createParameters($startDate, $startTime, $endDate, $endTime, $keyword));
        if (! empty($tweets->statuses)) {
            $untilDate = $this->getLastCreatedAt($tweets)[0];
            $untilTime = $this->getLastCreatedAt($tweets)[1];

            $tws = $this->retrieveAllTweets($twitter, $this->createParameters($startDate, $startTime, $untilDate, $untilTime, $keyword));
            while (! empty($tws->statuses)) {
                $tweets->statuses = array_merge($tweets->statuses, $tws->statuses);
                $untilDate = $this->getLastCreatedAt($tweets)[0];
                $untilTime = $this->getLastCreatedAt($tweets)[1];
                $tws = $this->retrieveAllTweets($twitter, $this->createParameters($startDate, $startTime, $untilDate, $untilTime, $keyword));
            }
        }
        
        return $tweets;
    }

    private function my_sort($a,$b)
    {
        if ($a->retweet_count == $b->retweet_count) {
            return 0;
        }
        return ($a->retweet_count > $b->retweet_count) ? -1:1;
    }

    private function sort($tweets) {
        if (isset($tweets->statuses)) {
            usort($tweets->statuses,array( $this, "my_sort" ));
            $tweets->statuses = array_slice($tweets->statuses, 0, self::MAX);
        }
        return $tweets;
    }

    private function getLastCreatedAt($tweets)
    {
        $count = count($tweets->statuses);
        $lastCreatedAt = new DateTime(($tweets->statuses)[$count-1]->created_at, new \DateTimeZone("UTC"));
        $lastCreatedAt->setTimezone(new \DateTimeZone('Asia/Tokyo'));

        return [$lastCreatedAt->format('Y-m-d'), $lastCreatedAt->format('H:i:s')];
    }

    private function createParameters($startDate, $startTime, $endDate, $endTime, $keyword)
    {
        return '?q=' . $keyword . 
            ' since:' . $this->changeSearchTimeZone($startDate, $startTime) . 
            ' until:' . $this->changeSearchTimeZone($endDate, $endTime) . 
            ' -filter:retweets and -filter:replies&count=100&tweet_mode=extended';
    }

    private function waitUntilReset($reset)
    {
        $time = time();
        usleep($reset - $time + 10);
    }
}
