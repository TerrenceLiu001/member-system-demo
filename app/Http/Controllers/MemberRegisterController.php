<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MemberRegister\MemberRegisterService;
use Exception;

class MemberRegisterController extends Controller
{
    protected MemberRegisterService $memberRegisterService;

    public function __construct(MemberRegisterService $memberRegisterService)
    {
        $this->memberRegisterService = $memberRegisterService;
    }

    // 處理「註冊」流程
    public function registerRun(Request $request)
    {
        try
        {
            $this->memberRegisterService->initiateRegistrationProcess($request);   
            return response()->json([
                'code' => 200, 
                'message' => '驗證信已寄出，請前往信箱完成開通流程'
            ]); 
        }
        catch (Exception $e)
        {
            return response()->json([
                'code' => '500', 
                'message' => $e->getMessage()
            ]);
        }
    }

    //  載入「密碼設定」頁面
    public function setPassword(Request $request)
    {
        try
        {
            $email = $request->route('email');
            $token = $request->route('token');

            $this->memberRegisterService->authorizeSetPasswordPage(
                $email, $token
            );

            return view('set_password', compact('email'));
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with(
                'error', $e->getMessage()
            );
        }
    }
    
    //  完成註冊
    public function completeRegistration(Request $request)
    {
        try
        {
            [ 
                'email'              => $email,
                'password'           => $password,
                'password_confirmed' => $comfirmed
            ] = $request->only([
                'email', 'password', 'password_confirmed'
            ]);

            $cookie = $this->memberRegisterService->completeRegistrationProcess(
                $email, $password, $comfirmed
            );
               
            return redirect()->route('set_account')->cookie($cookie); 
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with(
                'error', $e->getMessage()
            );
        }     
    }
}
