<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twitter;
use DateTime;
use Validator;

class RetrieveTweetsController extends Controller
{
    const KEYWORD_ERROR_MESSAGE = 'キーワードを入力して下さい';
    const TIME_WARNNING_MESSAGE = '※一週間以上経過したツイートは検索出来ません。ご注意下さい。';
    const TIME_ERROR_MESSAGE = '終了日付を開始日付より大きく指定して下さい。';
    public function getTweets(Request $request)
    {
        $errors = [];
        $warn = [];
        $startDate = '';
        $endDate = '';
        $startTime = '';
        $endTime = '';

        if (empty($request->keyword)) {
            $errors['keyword'] = self::KEYWORD_ERROR_MESSAGE;
        }
        if (! empty($request->start_time)) {
            $startDate = (new DateTime($request->start_time))->format('Y-m-d');
            \Log::info($startDate);
            $startTime = (new DateTime($request->start_time))->format('H:i:s');
            if (strtotime($startDate) < strtotime('-8 days')) {
                $warn['time'] = self::TIME_WARNNING_MESSAGE;
            }
        }
        if (! empty($request->end_time)) {
            $endDate = (new DateTime($request->end_time))->format('Y-m-d');
            $endTime = (new DateTime($request->end_time))->format('H:i:s');
            if (strtotime($endDate) < strtotime('-8 days')) {
                $warn['time'] = self::TIME_WARNNING_MESSAGE;
            }
        }
        if (strtotime($startDate) > strtotime($endDate)) {
            $errors['time'] = self::TIME_ERROR_MESSAGE;
        }
        if (! empty($errors)) {
            return response(['errors' => $errors]);
        }

        $parameters = $request->keyword . ' since:' . $startDate . '_' . $startTime . '_JST';
        $tweets = Twitter::getSearch(['q' => $parameters, 'until' => $endDate . '_' . $endTime . '_JST', 'count' => 100, 'format' => 'array']);
        $next_max_id = '';
        $next_results_url_params = ! empty($tweets['search_metadata']['next_results']) ? 
                                    preg_replace('/^\?/', '', $tweets['search_metadata']['next_results']) : 
                                    false;
        \Log::info($next_results_url_params);
        while (1) {
            if (empty($next_results_url_params)) {
                break;
            }
            $next_max_id = explode('&', explode('max_id=', $next_results_url_params)[1])[0];
            \Log::info($next_max_id);
            $next_results = Twitter::getSearch(['q' => $parameters, 'until' => $endDate . '_' . $endTime . '_JST', 'count' => 100, 'max_id' => $next_max_id, 'format' => 'array']);

            for ($j = 0; $j < count($next_results['statuses']); $j++) {
                $tweets['statuses'][] = $next_results['statuses'][$j];
            }
            $next_results_url_params = ! empty($next_results['search_metadata']['next_results']) ?
                                        preg_replace('/^\?/', '', $next_results['search_metadata']['next_results']) :
                                        false;
        }
        return response([
            'warns' => $warn,
            'tweets' => $tweets
        ]);
    }

    public function tweets(Request $request)
    {
        $userName = $request->user_name;
        return view('tweets', compact('userName'));
    }
}
