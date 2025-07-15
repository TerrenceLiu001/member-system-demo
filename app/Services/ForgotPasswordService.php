<?php

namespace App\Services;

use App\Models\PasswordUpdate;
use App\Models\User;  
use Illuminate\Support\Facades\DB; 
use Exception;

/**
 * 忘記密碼服務 (ForgotPasswordService)
 * 
 * - 驗證會員帳號有效性
 * - 建立並管理密碼重置請求
 * - 寄送重設密碼郵件含驗證連結
 * - 驗證重置請求與使用者權限
 * - 更新密碼
 *
 * ForgotPasswordController::forgotPasswordRun
 * ├─ isRequestValid()
 * └─ prepareVerification()
 *     └─ createAndUpdateTable() ← private
 *
 * ForgotPasswordController::resetPassword
 * └─ authorizeResetPasswordAccess()
 *     ├─ isRequestValid()
 *     └─ verifyPasswordToken() ← private
 *
 * ForgotPasswordController::resetConfirm
 * ├─ validateResetRequest()
 * │   └─ verifyPasswordToken() ← private
 * └─ resetPassword()
 *     
 *
 * @used-by \App\Http\Controllers\ForgotPasswordController
 *
 * 使用模型與對應資料表：
 * - User            → member_center_users              （正式會員資料）
 * - PasswordUpdate  → member_center_password_update    （密碼變更申請紀錄）
 */


class ForgotPasswordService
{
    // 檢查申請「忘記密碼」的帳號是否格式正確且已加入會員
    public static function isRequestValid(?string $account): void
    {
        ValidationService::validateEmail($account);
        if (!User::isRegistered($account)) throw new Exception('此信箱並非會員，請先註冊');
    }


    // 在 PasswordUpdate 中建立「忘記密碼」的請求，並寄送信件
    public static function prepareVerification(string $email): void
    {
        $record = self::createAndUpdateTable($email);
        $link = MemberEmailService::generateLink('reset_password', [
            'email' => $email,
            'token' => $record->password_token,
        ]);

        MemberEmailService::sendResetPasswordEmail($email, $link);
    }

    // 驗證使用者是否有權限載入「重設密碼」頁面
    public static function authorizeResetPasswordAccess(string $email, string $token): void
    {
        self::isRequestValid($email);
        $record = self::verifyPasswordToken($token);

        if (!$record) throw new Exception('連結無效，請重新流程');
        if ($record->email !== $email) throw new Exception('Email 與請求權杖並不一致');
        
    }

    // 驗證「重設密碼」頁面的表單內容是否正確
    public static function  validateResetRequest(?string $token, ?string $password, ?string $confirmed): void
    {
        if (!self::verifyPasswordToken($token)){
            throw new Exception('請求無效或已過期，請重新流程');
        } 

        ValidationService::checkPasswordInputs($password, $confirmed);
    } 


    // 在 User 中設定「新密碼」，並更新 PasswordUpdate 的 status 更新
    public static function resetPassword(string $token, string $password): void
    {
        DB::transaction(function () use ($token, $password) {

            $record = PasswordUpdate::where('password_token', $token)
                                    ->where('status', 'pending')
                                    ->where('type', 'forgot')->first();
            $record->complete();

            $user = User::find($record->user_id);
            $user->password = bcrypt($password);  
            $user->save();
            
            $bearerToken = MemberAuthService::generateToken('login');
            $user->updateTokenAndExpiry($bearerToken, 12);
        });
    }




    
    /** ----- 以下為私有方法 ----- */

    // 在 PasswordUpdate 中「創建」並「更新」重設密碼的請求
    private static function createAndUpdateTable(string $email): PasswordUpdate
    {
        $user = User::isRegistered($email);

        return DB::transaction( function () use ($user, $email){

            PasswordUpdate::cancelPendingForgot($user->id, $email);
            $record = PasswordUpdate::create([
                'user_id'  => $user->id,
                'email'    => $email,
                'type'     => 'forgot', 
                'status'   => 'pending' 
            ]);

            $token = MemberAuthService::generateToken('password');
            $record->updateTokenAndExpiry($token, 5);

            return $record;
        });
    }

    // 驗證「Password Token」
    private static function verifyPasswordToken(string $token): ?PasswordUpdate
    {
        $record = PasswordUpdate::where('password_token', $token)
                                ->where('type', 'forgot')
                                ->where('status', 'pending')->first();

        if (!$record) return null;
        if (!MemberAuthService::isTokenValid($record)) 
        {
            $record->status = 'expired';
            $record->save();
            throw new Exception("變更請求已過期，請重新流程");
        }
        return $record;      
    }
}