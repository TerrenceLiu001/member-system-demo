<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MemberRegisterService;
use App\Services\MemberAuthService;
use Exception;

class MemberRegisterController extends Controller
{


    // 處理「註冊」流程
    public function registerRun(Request $request)
    {
        try
        {
            MemberRegisterService::isRequestValid($request->post("account"));
            MemberRegisterService::prepareVerification($request->post("account"));

            return response()->json([
                'code' => 200, 
                'message' => '驗證信已寄出，請前往信箱完成開通流程'
            ]); 
        }
        catch (Exception $e)
        {
            return response()->json(['code' => '500', 'message' => $e->getMessage()]);
        }
    }

    //  載入「密碼設定」頁面
    public function setPassword(Request $request)
    {
        try
        {
            $email = $request->route('email');
            $token = $request->route('token');

            MemberRegisterService::authorizeSetPasswordAccess($email, $token);
            return view('set_password', compact('email'));
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }  
    
    //  加入會員
    public function createMember(Request $request)
    {
        try
        {
            $email     = $request->email;
            $password  = $request->password;
            $confirmed = $request->password_confirmed;

            MemberRegisterService::validateSetRequest($email, $password, $confirmed);
            $user = MemberRegisterService::createMember($email, $password);
            $cookie = MemberAuthService::setBearerTokenCookie($user->bearer_token, 2880);
            
            return redirect()->route('set_account')->cookie($cookie); 
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with('error', $e->getMessage());
        }     
    }

}
