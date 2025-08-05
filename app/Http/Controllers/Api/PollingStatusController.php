<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\PollingStatusService;
use Illuminate\Http\Request;
use Exception;

class PollingStatusController extends Controller
{
    // 輪詢是否已變更聯絡方式
    public function checkContactUpdateStatus(Request $request)
    {
        try
        {
            $data = PollingStatusService::isRequestValid($request);
            [ 'is_updated' => $isUpdated, 'status' => $status]  = PollingStatusService::checkUpdateStatus($data);


            if ($isUpdated){
                return response()->json(['code' => 200, 'message' => 'done', 'status'=> $status]);
            };
            return response()->json(['code' => 200, 'message' => 'undone', 'status' => $status]);

        }
        catch ( Exception $e)
        {
            return response()->json([ 'code' => 400, 'message' => $e->getMessage()]);
        } 
    }
}
