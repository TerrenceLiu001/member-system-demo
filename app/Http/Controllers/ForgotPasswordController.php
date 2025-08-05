<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ForgotPassword\ForgotPasswordService;
use Exception;

class ForgotPasswordController extends Controller
{

    protected ForgotPasswordService $forgotPasswordService;
    public function __construct(ForgotPasswordService $forgotPasswordService)
    {
        $this->forgotPasswordService = $forgotPasswordService;   
    }
    
    // 載入「重設密碼」頁面
    public function forgotPassword(Request $request)
    {
        return view('forgot_password');
    }

    // 執行「忘記密碼」流程
    public function forgotPasswordRun(Request $request)
    {
        try
        {
            $this->forgotPasswordService->initiateForgotPasswordProcess($request);
            return response()->json([
                'code'    => 200, 
                'message' => '變更密碼信件已寄出，請前往信箱查收'
            ]); 

        } catch (Exception $e)
        {
            return response()->json([
                'code'    => 500, 
                'message' => $e->getMessage()
            ]);
        }
    }

    // 載入「重設密碼」頁面
    public function resetPassword(Request $request)
    {
        try
        {
            $email = $request->route('email');
            $token = $request->route('token');

            $this->forgotPasswordService->authorizeSetPasswordPage(
                $email, $token
            );

            return view(
                'reset_password', compact('token')
            );

        }catch (Exception $e)
        {
            return redirect()->route('login')->with(
                'error', $e->getMessage()
            );
        }
    }

    // 設定「新密碼」
    public function resetConfirm(Request $request)
    {
        try
        {
            [
                'token'              => $token,
                'password'           => $password,
                'password_confirmed' => $confirmed
            ] = $request->only([
                'token', 'password', 'password_confirmed'
            ]);

            $this->forgotPasswordService->completeResetPasswordProcess(
                $token, $password, $confirmed
            );

            return redirect()->route('complete_confirm');
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with(
                'error', $e->getMessage()
            );
        }
    }
}