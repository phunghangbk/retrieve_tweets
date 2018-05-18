<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twitter;
use DateTime;

class RetrieveTweetsController extends Controller
{
    const STATUS_ERROR = 'error';
    const STATUS_SUCCESS = 'success';

    public function getTweets(Request $request)
    {
        $this->validate($request, [
            'keyword' => 'required|string',
        ]);
        $end_date = '';
        if ($request->end_time !== '') {
            $end_date = new DateTime($request->end_time);
            $end_date->format('Y-m-d');
        }
        $tweets = Twitter::getSearch(['q' => $request->keyword, 'until' => $end_date, 'format' => 'array']);
        var_dump($tweets);exit;
        // return view('tweets',compact('tweets'));
    }
}
