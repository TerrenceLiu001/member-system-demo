<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\MemberAuthService;
use App\Models\User;
use Exception;

class MemberCenterPathMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route() ? $request->route()->getName() : NULL;

        $allowedRouteNames = [

            'login',                  //  載入「登入註冊」頁面
            'login_run',              //  處理「登入」流程
            'update_contact',         //  載入「確認變更」頁面  
            'button_confirm',         //  執行「變更/取消」通訊帳號流程
            'complete_confirm',       //  載入「完成變更」頁面
        ];

        if (in_array($routeName, $allowedRouteNames)){
            return $next($request);
        }

        try
        {
            $token = $request->cookie('bearer_token');
            if (!$token) return redirect()->route('login')->with('error', '請先登入');

            $user = MemberAuthService::verifyToken($token, 'login');
            // if (!$user) return redirect()->route('login')->with('error', '登入狀態已過期，請重新登入');

            $request->attributes->set('user', $user);
            return $next($request); 
            
        }
        catch (Exception $errorMessage)
        {
            return redirect()->route('login')->with('error', $errorMessage->getMessage());
        }
    }
}
