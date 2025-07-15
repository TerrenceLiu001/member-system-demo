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
            'register_run',           //  處理「註冊」流程
            'login_run',              //  處理「登入」流程
            'set_password',           //  載入「密碼設定」頁面
            'create_member',          //  新增「會員帳號」
            'update_contact',         //  載入「確認變更」頁面  
            'update_confirm',         //  執行「確認」變更
            'cancel_confirm',         //  執行「取消」變更
            'complete_confirm',       //  載入「完成變更」頁面
            'forgot_password',        //  載入「忘記密碼」頁面
            'forgot_password_run',    //  執行「忘記密碼」流程
            'reset_password',         //  載入「重設密碼」頁面
            'reset_confirm',          //  設定「新密碼」  
        ];

        if (in_array($routeName, $allowedRouteNames)){
            return $next($request);
        }

        try
        {
            $token = $request->cookie('bearer_token');
            if (!$token) return redirect()->route('login')->with('error', '請先登入');

            $user = MemberAuthService::validateUserLogin($token);
            if (!$user) return redirect()->route('login')->with('error', '登入狀態已過期，請重新登入');

            $request->attributes->set('user', $user);
            return $next($request); 
            
        }
        catch (Exception $errorMessage)
        {
            return redirect()->route('login')->with('error', $errorMessage->getMessage());
        }
    }
}
