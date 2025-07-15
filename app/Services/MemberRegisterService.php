<?php

namespace App\Services;

use App\Models\User;  
use App\Models\Guest; 
use Illuminate\Support\Facades\DB; 
use Exception;  

/**
 * 會員註冊服務 (MemberRegisterService)
 *
 * - 驗證註冊請求
 * - 發送註冊驗證信
 * - 完成會員正式註冊，建立會員資料 
 *
 * 
 * MemberRegisterController::registerRun
 * ├─ isRequestValid()
 * └─ prepareVerification()
 *    └─createAndUpdateGuest()  ← private
 *
 * 
 * MemberRegisterController::setPassword
 * └─ authorizeSetPasswordAccess()
 *    ├─isRequestValid()
 *    └─verifyRegisterToken()  ← private
 * 
 * 
 * MemberRegisterController::createMember
 * ├─ validateSetRequest()
 * |   ├─ isRequestValid()
 * |   └─ verifyRegisterToken()  ← private
 * └─ createMember()
 *
 * @used-by \App\Http\Controllers\MemberRegisterController
 * 
 * 使用模型與對應資料表：
 * - Guest → member_center_guests    （尚未註冊完成的訪客暫存資料）
 * - User  → member_center_users     （已註冊之正式會員）
 */


class MemberRegisterService
{


    // 檢查註冊帳號是否格式正確且可以註冊
    public static function isRequestValid(?string $account): void
    {
        ValidationService::validateEmail($account);
        if (User::isRegistered($account)) throw new Exception('此信箱已被註冊，請直接登入');
    }


    // 建立訪客資料並寄送註冊信件
    public static function prepareVerification(string $email): void
    {
        $guest = self::createAndUpdateGuest($email);
        $link = MemberEmailService::generateLink('set_password', [
            'email' => $email,
            'token' => $guest->register_token,
        ]);
        MemberEmailService::sendRegisterEmail($email, $link);
    }

    // 驗證使用者是否有權限載入「設定密碼」頁面
    public static function authorizeSetPasswordAccess(string $email, string $token): void
    {
        self::isRequestValid($email);
        $guest = self::verifyRegisterToken($token);

        if (!$guest) throw new Exception("連結無效，請重新流程");
        if ($guest->email !== $email) throw new Exception('Email 與請求權杖並不一致');
    }


    // 驗證「設定密碼」頁面的表單內容是否正確
    public static function  validateSetRequest(?string $email, ?string $password, ?string $confirmed): void
    {
        self::isRequestValid($email);
        $guest = Guest::where('email', $email)
                      ->where('status', 'pending')->first();

        if (!self::verifyRegisterToken($guest->register_token))
        {
            throw new Exception('請求無效或已過期，請重新流程');
        }    

        ValidationService::checkPasswordInputs($password, $confirmed);
    }


    // 在 User 中建立新帳號，並設定 Bearer Token
    public static function createMember(string $email, string $password): User
    {

        return DB::transaction(function() use ($email, $password) 
        {
            $guest = Guest::where('email', $email)
                          ->where('status', 'pending')->first();

            $user = User::create([
                'email'            => $email,
                'password'         => bcrypt($password),
                'guest_id'         => $guest->id,
            ]);

            $bearerToken = MemberAuthService::generateToken('login');
            $user->updateTokenAndExpiry($bearerToken, 12);

            return $user;
        });
    }


    /** ----- 以下為私有方法 ----- */


    // 在 Guest 中「創建」並「更新」資料
     private static function createAndUpdateGuest(string $email): Guest
    {
        return DB::transaction(function () use ($email) {

            Guest::cancelPending($email);
            $guest = Guest::create([
                'email'  => $email,
                'status' => 'pending'
            ]);

            $token = MemberAuthService::generateToken('register');
            $guest->updateTokenAndExpiry($token, 10);

            return $guest;
        });
    }

    // 驗證「Register Token」
    private static function verifyRegisterToken(?string $token): ?Guest
    {
        $guest = Guest::where('register_token', $token)
                      ->where('status', 'pending')->first();

        if (!$guest) return null;
        if (!MemberAuthService::isTokenValid($guest))
        {
            $guest->status = 'expired';
            $guest->save();
            throw new Exception("驗證連結已過期，請重新註冊");
        }
        return $guest;
    }
}
