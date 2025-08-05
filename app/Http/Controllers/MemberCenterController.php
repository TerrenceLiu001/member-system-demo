<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MemberAuthService;
use App\Services\MemberEditService;
use App\Services\MemberLoginService;
use Exception;

class MemberCenterController extends Controller
{
    // 載入「登入註冊」頁面
    public function login(Request $request)
    {
        return view('login');
    }

    // 處理「登入」流程
    public function loginRun(Request $request)
    {
        try
        {
            $user = MemberLoginService::verifyLoginRequest($request);
            if (!$user) return response()->json(['code' => '401', 'message' => '帳號或密碼錯誤，請重新輸入']);

            $user = MemberLoginService::setLogin($user);
            $cookie = MemberAuthService::setBearerTokenCookie($user->bearer_token, 2880);

            if (!$user->username)
            {
                return response()->json([ 'code'    => '302', 
                                          'message' => '上次未完成註冊流程，請先設定帳戶資料', 
                                          'url'     => route('set_account')
                ])->cookie($cookie);
            } 
            
            return response()->json(['code' => '200', 'message' => '成功', 'url' => '/member_home'])->cookie($cookie); 

        }
        catch (Exception $e)
        {
            return response()->json(['code' => '500', 'message' => $e->getMessage()]);
        }
    }

    //  載入「會員中心首頁」頁面
    public function memberHome(Request $request)
    {
        $user = $request->attributes->get('user');
        return view('member_home', ['username' => $user->username]);
    }

    //  載入「設定帳號」的頁面
    public function setAccount(Request $request)
    {
        $user = $request->attributes->get('user');
        return view('member_data', ['user' => $user]);
    }


    // 編輯「會員資料」
    public function editMemberData(Request $request)
    {
        try
        {
            $user = $request->attributes->get('user');

            MemberEditService::isRequestValid($user, $request);
            MemberEditService::updateMemberData($user, $request);

            return response()->json(['code' => 200, 'message' => 'success', 'url' => '/member_home']);
        }
        catch ( Exception $e)
        {
            return response()->json(['code' => 400, 'message' => $e->getMessage() ]);
        }
    }

    // 登出
    public function logout(Request $request)
    {

        $user = $request->attributes->get('user');
        if ($user) MemberLoginService::logout($user);

        $cookie = MemberAuthService::forgetBearerToken();

        return response()->json(['message' => '登出成功'])->cookie($cookie);

    }

}
