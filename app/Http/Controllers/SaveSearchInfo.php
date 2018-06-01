<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SearchInfo;
use DateTime;
use Exception;

class SaveSearchInfo extends Controller
{
    const ERROR_MESSAGE = '検索履歴を保存できません。';
    const ERROR_STATUS = 'error';
    const SUCCESS_MESSAGE = '検索履歴を保存できました。';
    const SUCCESS_STATUS = 'success';

    public function savesearchinfo(Request $request)
    {
        try {
            $searchinfo = new SearchInfo();
            $searchinfo->user_name = $this->userName($request);
            $searchinfo->searched_at = $this->searchedAt($request);
            $searchinfo->keyword = $this->keyword($request);
            $searchinfo->start = $this->start($request);
            $searchinfo->end = $this->end($request);
            $searchinfo->status = $this->status($request);

            if (! $searchinfo->save()) {
                return response([
                    'status' => self::ERROR_STATUS,
                    'message' => self::ERROR_MESSAGE
                ]);
            }

            return response([
                'status' => self::SUCCESS_STATUS,
                'message' => self::SUCCESS_MESSAGE
            ]);
        } catch (Exception $e) {
            return response([
                'status' => self::ERROR_STATUS,
                'message' => self::ERROR_MESSAGE
            ]);
        }
    }

    private function userName($request)
    {
        return ! empty($request->user_name) ? $request->user_name : '';
    }

    private function searchedAt($request)
    {
        return ! empty($request->searched_at) ? $request->searched_at : '';
    }

    private function keyword($request)
    {
        return ! empty($request->keyword) ? $request->keyword : '';
    }

    private function start($request)
    {
        return ! empty($request->start) ? $request->start : '';
    }

    private function end($request)
    {
        return ! empty($request->end) ? $request->end : '';
    }

    private function status($request)
    {
        return ! empty($request->status) ? $request->status : '';
    }
}
