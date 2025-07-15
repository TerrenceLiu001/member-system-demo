<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ForgotPasswordService;
use Exception;

class ForgotPasswordController extends Controller
{

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
            ForgotPasswordService::isRequestValid($request->email);
            ForgotPasswordService::prepareVerification($request->email);
            return response()->json([
                'code' => 200, 
                'message' => '變更密碼信件已寄出，請前往信箱查收'
            ]); 

        } catch (Exception $e)
        {
             return response()->json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    // 載入「重設密碼」頁面
    public function resetPassword(Request $request)
    {
        try
        {
            $email = $request->route('email');
            $token = $request->route('token');

            ForgotPasswordService::authorizeResetPasswordAccess($email, $token);
            return view('reset_password', compact('token'));

        }catch (Exception $e)
        {
            return redirect()->route('login')->with('error', $e->getMessage());
        }

    }

    // 設定「新密碼」
    public function resetConfirm(Request $request)
    {
        try
        {
            $token = $request->token;
            $password  = $request->password;
            $confirmed = $request->password_confirmed;

            ForgotPasswordService::validateResetRequest($token, $password, $confirmed);
            ForgotPasswordService::resetPassword($token, $password);
            return redirect()->route('complete_confirm');

        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }

}