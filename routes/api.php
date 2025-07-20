<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PollingStatusController;



Route::controller(PollingStatusController::class)->group(function() 
{
    Route::post('/contact/update/status', 'checkContactUpdateStatus');  // 輪詢「通訊」變更狀態
});
