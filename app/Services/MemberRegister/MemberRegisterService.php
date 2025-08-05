<?php

namespace App\Services\MemberRegister;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use App\Services\Strategies\Verification\VerificationEmailOrchestrator;
use App\Services\MemberRegister\UnitRegisterService;



/**
 * 會員註冊服務 (MemberRegisterService)
 *
 * - 接收來自 Controller 的註冊流程請求
 * - 呼叫通用流程 Orchestrator 執行與「註冊」相關的策略
 * 
 *
 * MemberRegisterController::registerRun
 * └─ initiateRegistrationProcess()
 *    └─ dispatchVerification()     ← VerificationEmailOrchestrator
 *       └─ RegisterVerificationStrategy::
 *s
 * MemberRegisterController::setPassword
 * └─ authorizeSetPasswordPage()
 *    ├─ ensureAccountValid()   ← UnitRegisterService
 *    └─ verifyRegisterToken()  ← UnitRegisterService
 *
 * MemberRegisterController::completeRegistration
 * └─ completeRegistrationProcess()
 *    ├─ ensureDataValid()  ← UnitRegisterService
 *    ├─ createMember()     ← UnitRegisterService
 *    └─ setCookie()        ← UnitRegisterService
 * 
 *
 *
 * @used-by \App\Http\Controllers\MemberRegisterController
 *
 * 使用模型與對應資料表：
 * - Guest → member_center_guests    （尚未註冊完成的訪客暫存資料）
 * - User  → member_center_users     （已註冊之正式會員）
 */


class MemberRegisterService
{
    protected VerificationEmailOrchestrator $orchestrator;
    protected UnitRegisterService $unitService; 


    public function __construct(
        VerificationEmailOrchestrator $orchestrator,
        UnitRegisterService $unitService
    )
    {
        $this->orchestrator = $orchestrator;
        $this->unitService  = $unitService;
    }


    // 開始「註冊」流程
    public function initiateRegistrationProcess(Request $request): void
    {
        $this->orchestrator->dispatchVerification(
            'register', 
            $request
        );
    }

    // 載入「設定密碼」頁面
    public function authorizeSetPasswordPage(string $email, string $token): void
    {
        $this->unitService->ensureAccountValid($email);
        $this->unitService->verifyRegisterToken(
            $token, $email
        );
    }

    // 完成「註冊」
    public function completeRegistrationProcess(?string $email, ?string $password, ?string $confirmed): Cookie
    {

        $validatedData = $this->unitService->ensureDataValid(
            $email, $password, $confirmed
        );

        $user = $this->unitService->createMember($validatedData);
        $cookie = $this->unitService->setCookie($user->bearer_token);

        return $cookie;
    }
}
