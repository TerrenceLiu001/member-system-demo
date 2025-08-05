<?php

namespace App\Services\UpdateContact;


use Illuminate\Http\Request;
use App\Services\Strategies\Verification\VerificationEmailOrchestrator;
use App\Services\UpdateContact\UnitUpdateContactService;

/**
 * 通訊資料變更服務 (UpdateContactService)
 *
 * - 接收來自 Controller 的「變更 Email / 手機」請求
 * - 呼叫通用流程 Orchestrator 執行與「變更通訊資料」相關的策略
 * 
 *
 * UpdateContactController::updateEmail
 * └─ initiateUpdateContactProcess()
 *    └─ dispatchVerification()  ← VerificationEmailOrchestrator 
 *       └─ UpdateContactVerificationStrategy::
 *
 * UpdateContactController::updateContact
 * └─ authorizeUpdateContactPage()
 *    ├─ findUserByAccount()            ← UnitUpdateContactService
 *    ├─ verifyUpdateContactToken()     ← UnitUpdateContactService
 *    └─ ensureRecordMatchesUser()      ← UnitUpdateContactService
 *
 * UpdateContactController::buttonConfirm
 * └─ handleConfirmation()
 *    ├─ ensureDataValid()                      ← UnitUpdateContactService
 *    └─ completedUpdate() or cancelUpdate()    ← UnitUpdateContactService
 * 
 *
 * @used-by \App\Http\Controllers\UpdateContactController
 *
 * 使用模型與對應資料表：
 * - UserContactUpdate → member_center_user_contact_updates
 *     （暫存會員欲變更的通訊資料）
 */

class UpdateContactService
{
    protected VerificationEmailOrchestrator $orchestrator;
    protected UnitUpdateContactService $unitService;

    public function __construct(
        VerificationEmailOrchestrator $orchestrator,
        UnitUpdateContactService $unitService
    )
    {
        $this->orchestrator = $orchestrator;
        $this->unitService  = $unitService;
    }

    // 開始變更「通訊流程」
    public function initiateUpdateContactProcess(Request $request): void
    {
        $this->orchestrator->dispatchVerification(
            'update_contact', 
            $request
        );
    }


    // 載入「變更通訊」頁面
    public function authorizeUpdateContactPage(string $email, string $token): array
    {

        $user = $this->unitService->findUserByAccount($email);
        $record = $this->unitService->verifyUpdateContactToken($token);

        $this->unitService->ensureRecordMatchesUser(
            $user, $record, $email
        );

        return [
            'email'        => $record->email, 
            'new_contact'  => $record->new_contact,
            'contact_type' => $record->contact_type,
            'token'        => $record->update_contact_token
        ];
    }


    // 「完成」或「取消」變更
    public function handleConfirmation(array $data): array
    {

        [
            'record' => $record, 
            'action' => $action
        ] = $this->unitService->ensureDataValid($data);


        $handler = match ($action) 
        {
            'completed' => function () use ($record) {
                $this->unitService->completedUpdate($record);
                return ['route' => 'complete_confirm', 'session' => [null, null]];
            },

            'cancel' => function () use ($record) {
                $this->unitService->cancelUpdate($record);
                return ['route' => 'login', 'session' => ['success', '已成功取消變更']];
            },

            default => fn() => ['route' => 'login', 'session' => ['error', '未知錯誤']]
        };

        return $handler();
    }
}