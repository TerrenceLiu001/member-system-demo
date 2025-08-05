<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MemberLogin\MemberLoginService;
use Exception;

class MemberLoginController extends Controller
{
    protected MemberLoginService $memberLoginService;

    public function __construct(MemberLoginService $memberLoginService)
    {
        $this->memberLoginService = $memberLoginService;
    }

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
            $requestData = $request->only([
                'account','password','phone_identifier_code'
            ]);

            [ $responseData, $cookie ] = $this->memberLoginService->handleLogin($requestData);
            $response = response()->json($responseData);

            return $cookie ? $response->cookie($cookie):$response;
        }
        catch (Exception $e)
        {
            return response()->json([
                'code'    => '500', 
                'message' => $e->getMessage()
            ]);
        }
    }

    // 登出
    public function logout(Request $request)
    {
        $user = $request->attributes->get('user');
        $cookie = $this->memberLoginService->handleLogout($user);
        return response()->json(['message' => '登出成功'])->cookie($cookie);
    }
    
}