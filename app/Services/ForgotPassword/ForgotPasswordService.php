<?php

namespace App\Services\ForgotPassword;

use Illuminate\Http\Request;
use App\Services\Strategies\Verification\VerificationEmailOrchestrator;
use App\Services\ForgotPassword\UnitForgotPasswordService;

/**
 * 忘記密碼服務 (ForgotPasswordService)
 * 
 * - 接收來自 Controller 的註冊流程請求
 * - 呼叫通用流程 Orchestrator 執行與「忘記密碼」相關的策略
 *
 * 
 * ForgotPasswordController::forgotPasswordRun
 * └─ initiateForgotPasswordVProcess()
 *    └─ dispatchVerification()  ← VerificationEmailOrchestrator 
 *       └─ ForgotPasswordVerificationStrategy::
 * 
 * ForgotPasswordController::resetPassword
 * └─ authorizeSetPasswordPage()
 *     ├─ ensureAccountValid()      ← UnitForgotPasswordService
 *     └─ verifyPasswordToken()     ← UnitForgotPasswordService
 *
 * ForgotPasswordController::resetConfirm
 * └─ completeResetPasswordProcess()
 *    ├─ ensureDataValid()      ← UnitForgotPasswordService
 *    └─ resetPassword()        ← UnitForgotPasswordService
 *
 * @used-by \App\Http\Controllers\ForgotPasswordController
 *
 * 使用模型與對應資料表：
 * - User            → member_center_users              （正式會員資料）
 * - PasswordUpdate  → member_center_password_update    （密碼變更申請紀錄）
 */


class ForgotPasswordService
{
    protected VerificationEmailOrchestrator $orchestrator;
    protected UnitForgotPasswordService $unitService;

    public function __construct(
        VerificationEmailOrchestrator $orchestrator,
        UnitForgotPasswordService $unitService
    )
    {
        $this->orchestrator = $orchestrator;
        $this->unitService  = $unitService;
    }


    // 開始「忘記密碼」流程
    public function initiateForgotPasswordProcess(Request $request): void
    {
        $this->orchestrator->dispatchVerification(
            'forgot_password', 
            $request
        );
    }


    // 載入「重設密碼」頁面
    public function authorizeSetPasswordPage(string $email, string $token): void
    {
        $this->unitService->ensureAccountValid($email);
        $this->unitService->verifyPasswordToken(
            $token, $email
        );
    }

    // 完成「重設密碼」
    public function completeResetPasswordProcess(?string $token, ?string $password, ?string $confirmed): void
    {
        $validateData = $this->unitService->ensureDataValid(
            $token, $password, $confirmed
        );

        $this->unitService->resetPassword($validateData);
    }

}