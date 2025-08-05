<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Illuminate\Support\Str; 
use App\Contracts\MemberTokenInterface;
use App\Models\User;  
use App\Models\Guest;
use App\Models\PasswordUpdate;
use App\Models\UserContactUpdate; 
use Carbon\Carbon;
use Exception;


class MemberAuthService
{    
    
    // 生成 Token 
    public static function generateToken(string $methodName, ?int $length = 64): string
    {
        do {
            $token = Str::random($length);
            $exist = self::searchByToken($methodName, $token);
        } while ($exist);

        return $token;
    }

    // 依「方法」和 Token 回傳資料 
    public static function searchByToken(string $methodName, string $token): ?MemberTokenInterface
    {
        $modelClass = self::resolveModelClass($methodName);
        return $modelClass['modelName']::where($modelClass['tokenName'], $token)->first();
    }

    // 由「Bearer Token」來確認用戶的登入資格
    public static function validateUserLogin(string $token): ?User
    {
        $user = self::searchByToken('login', $token);
        if (!$user||!self::isTokenValid($user)) return null;

        return $user;
    }

    // 將「Bearer Token」 設置在「Cookie」中   
    public static function setBearerTokenCookie(string $token, int $time): SymfonyCookie
    {
        return cookie('bearer_token', $token, $time);
    }

    // 清空 cookie 中的 Bearer Token
    public static function forgetBearerToken(): SymfonyCookie
    {
        return Cookie::forget('bearer_token');
    }

    // 驗證 Token 是否到期
    public static function isTokenValid(MemberTokenInterface $model): bool
    {
        $expiry = $model->getTokenExpiresAt();
        if (!$expiry) return false;

        return Carbon::now()->isBefore($expiry);
    }

    // 依「方法」解析對應的 Model 以及 Token 名字
    private static function resolveModelClass(string $methodName): array
    {
        return match ($methodName) {

            'register'     => [
                'modelName' => Guest::class, 
                'tokenName' => 'register_token'
            ],
            'login'        => [
                'modelName' => User::class, 
                'tokenName' => 'bearer_token'
            ],
            'update_contact' => [
                'modelName' => UserContactUpdate::class,
                'tokenName' => 'update_contact_token'    
            ],
            'password'        => [
                'modelName' => PasswordUpdate::class, 
                'tokenName' => 'password_token'
            ],
            default    => throw new Exception(" 此操作未搜尋到匹配的 Model"),
        };
    }

}