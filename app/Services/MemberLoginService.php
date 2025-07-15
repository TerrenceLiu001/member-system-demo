<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Exception;

/**
 * 會員登入服務（MemberLoginService）
 *
 * - 驗證帳號密碼格式
 * - 帳密比對
 * - 寫入 Bearer Token
 * - 登出清除 Token
 *
 * 對應 Controller 流程如下：
 *
 * MemberCenterController::loginRun
 * ├─ verifyLoginRequest()
 * │   ├─ checkLoginType()         ← private
 * │   ├─ validateInput()          ← private
 * │   └─ validateCredentialsByType() ← private
 * │       ├─ authenticateByEmail()   ← private
 * │       └─ authenticateByMobile()  ← private
 * │
 * └─ setLogin()
 * 
 * MemberCenterController::logout
 * └─ logout()
 *  
 * @used-by \App\Http\Controllers\MemberCenterController
 * 
 * 使用模型與對應資料表：
 * - User  → member_center_users     （已註冊之正式會員）
 */


class MemberLoginService
{    

    // 登入
    public static function verifyLoginRequest(Request $request): ?User
    {
        $account   = $request->post("account");
        $password  = $request->post("password");
        $phoneCode = $request->post("phone_identifier_code");

        $type = self::checkLoginType($phoneCode);
        self::validateInput($account, $password, $type);

        return self::validateCredentialsByType($account, $password, $type);
    }


    // 設定登入狀態
    public static function setLogin(User $user): User
    {
        $bearerToken = MemberAuthService::generateToken('login');
        $user->updateTokenAndExpiry($bearerToken, 12);

        return $user;
    }

    // 登出
    public static function logout(User $user):void
    {
        $user->update([
            'bearer_token' => null,
            'token_expires_at' => null
        ]);
    }


    /** ----- 以下為私有方法 ----- */


    // 檢查登入類型
    private static function checkLoginType(?string $phoneCode): string
    {
        return match ($phoneCode) 
        {
            '+886'  => 'TWN',
            '+82'   => 'KOR', 
            '+81'   => 'JPN', 
            default => 'email',
        };
    } 

    // 確認請求是否有效
    private static function validateInput(string $account, string $password, string $type): void
    {
        if (!$account) throw new Exception("請輸入帳號");
        if (!$password) throw new Exception("請輸入密碼");

        match ($type) 
        {
            'email'   => ValidationService::validateEmail($account),
             default  => ValidationService::validateMobile($type, $account),
        };
    }

    // 驗證密碼
    private static function validateCredentialsByType(string $account, string $password, string $type): ?User
    {
        return ($type === 'email')? self::authenticateByEmail($account, $password)
                                  : self::authenticateByMobile($account, $password, $type);
    }

    // 用電子郵件，來比對用戶的密碼
    private static function authenticateByEmail(string $email, string $password): ?User
    {
        $user = User::isRegistered($email);
        if (!$user) throw new Exception("尚未加入會員，請先註冊");

        return (Hash::check($password, $user->password))? $user : null;
    }

     // 用手機號碼，來比對用戶的密碼
    private static function authenticateByMobile(string $mobile, string $password, string $type): ?User
    {
        $user = User::isRegistered($mobile, $type);
        if (!$user) throw new Exception("尚未加入會員，請先註冊");

        if ($user->country !== $type) throw new Exception("手機區碼與資料庫不符");

        return (Hash::check($password, $user->password))? $user : null;
    }
}