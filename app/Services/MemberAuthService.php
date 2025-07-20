<?php

namespace App\Services;

use App\Contracts\Model\Tokens\TokenCapableInterface;
use App\Services\Strategies\Tokens\TokenStrategyRegistry;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Exception;

/**
 * 會員權限與 Token 驗證服務
 *
 * 功能包含：
 * - 各類驗證 Token 的生成、查詢與過期判斷
 * - 登入用 Bearer Token 的處理（設定/清除）
 * - 驗證註冊、通訊變更、密碼重設等流程的有效性
 */


class MemberAuthService
{    
    
    // 生成 Token 
    public static function generateToken(string $methodName, ?int $length = 64): string
    {
        $strategy = TokenStrategyRegistry::get($methodName);
        return $strategy->generateToken($length); 
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

    // 驗證 Token 是否有效
    public static function verifyToken(TokenCapableInterface|string $input, string $method, ?array $scopes = []): ?TokenCapableInterface 
    {

        $strategy = TokenStrategyRegistry::get($method);
        $model = is_string($input)? $strategy->resolveModel($input, $scopes): $input;

        if (!$model) throw new Exception($strategy->getInvalidMessage()); 
        if ($strategy->isExpired($model)) 
        {
            $strategy->handleExpired($model);
            throw new Exception($strategy->getExpiredMessage());
        }

        return $model; 
    }

}