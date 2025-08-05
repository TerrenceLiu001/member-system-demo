<?php

namespace App\Services\MemberLogin;


use Symfony\Component\HttpFoundation\Cookie;
use App\Models\User;
use App\Services\MemberLogin\UnitLoginService;

/**
 * 會員登入服務（MemberLoginService）
 *
 * - 執行「登入」流程
 * - 帳密比對
 * - 寫入 Bearer Token
 * - 登出清除 Token
 *
 * 對應 Controller 流程如下：
 *
 * MemberLoginController::loginRun
 * └─ handleLogin()
 *    ├─ ensureRequestValid()   ← private
 *    │  ├─ checkLoginType()    ← UnitLoginService
 *    │  └─ validateInput()     ← UnitLoginService
 *    ├─ verifyCredentials()    ← UnitLoginService    
 *    └─ setLogin() ← UnitLoginService
 *       
 * 
 * 
 * MemberCenterController::logout
 * └─ handleLogout()
 *    └─ setLogout()    ← UnitLoginService 
 *  
 * 
 * @used-by \App\Http\Controllers\MemberLogin\MemberLoginController
 * 
 */


class MemberLoginService
{
    protected UnitLoginService $unitService;

    public function __construct(UnitLoginService $unitService)
    {
        $this->unitService = $unitService;
    }

    // 執行「登入流程」
    public function handleLogin(array $requestData): array
    {
        [
            'account'    => $account,
            'password'   => $password,
            'type'       => $type
        ] = $this->ensureRequestValid($requestData);

        // 驗證「帳號」、「密碼」
        $user = $this->unitService->verifyCredentials(
            $account, $password, $type
        );

        if (!$user){
            return [[
                'code' => '401', 
                'message' => '帳號或密碼錯誤，請重新輸入'], null
            ];
        }

        // 設定「登入」
        $cookie = $this->unitService->setLogin($user);

        // 檢查是否有完成「註冊流程」
        if (!$user->username) {
            return [[
                    'code'    => '302',
                    'message' => '上次未完成註冊流程，請先設定帳戶資料',
                    'url'     => route('set_account')
                ], $cookie
            ];
        }

        // 登入成功
        return [[
                'code'    => '200',
                'message' => '成功',
                'url'     => route('member_home')
            ], $cookie
        ];
    }


    // 登出
    public function handleLogout(User $user): Cookie 
    {
        $cookie = $this->unitService->setLogout($user);
        return $cookie;
    }

    /** ----- 以下為私有方法 ----- */

    // 檢查請求是否正確
    private function ensureRequestValid(array $requestData): array
    {
        [
            'account'               => $account,
            'password'              => $password,
            'phone_identifier_code' => $phoneCode
        ] = $requestData;


        $type = $this->unitService->checkLoginType($phoneCode);
        $this->unitService->validateInput(
            $account, $password, $type
        );

        return [
            'account'    => $account,
            'password'   => $password,
            'type'       => $type
        ];
    }
}