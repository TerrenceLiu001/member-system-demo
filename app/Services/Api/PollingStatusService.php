<?php

namespace App\Services\Api;

use App\Models\User;
use App\Models\UserContactUpdate; 
use App\Services\MemberAuthService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Exception;

/**
 * 輪詢狀態服務 (PollingStatusService)
 * 
 * - 驗證請求與身份
 * - 查詢變更狀態
 * - 回傳更新結果
 * 
 * 主要流程：
 * 
 * PollingStatusController::checkContactUpdateStatus
 * ├─ isRequestValid() 
 * |   └─ resolveContact()  ←private
 * └─ checkUpdateStatus() 
 *     ├─ searchPollingRecord()  ←private
 *     └─ isUpdate()   ←private
 * 
 * @used-by \App\Http\Controllers\PollingStatusController
 */


class PollingStatusService
{

    protected MemberAuthService $memberAuthService;
    protected ValidationService $validationService;

    public function __construct(MemberAuthService $memberAuthService, ValidationService $validationService)
    {
        $this->memberAuthService = $memberAuthService;
        $this->validationService = $validationService;        
    }


    // 驗證請求，並取得使用者物件
    public function isRequestValid(Request $request): array
    {
        $user = $this->memberAuthService->verifyToken($request->bearerToken(), 'login');
        if(!$user) throw new Exception('身份驗證失敗，請重新登入');

        [$contact, $type] = $this->resolveContact($request);
        
        return ['user' => $user, 'new_contact' => $contact, 'contact_type' => $type];
    }


    // 查詢「變更通訊」紀錄的 Status
    public function checkUpdateStatus(array $array): array
    {
        $record = $this->searchPollingRecord($array);
        if (!$record) throw new Exception("操作錯誤，請重新點擊「驗證」按鈕");

        $isUpdated = $this->isUpdate($record, $array['user']);
        return [ 'is_updated' => $isUpdated, 'status' => $record->status ];
    }


    /** ----- 以下為私有方法 ----- */


    // 提取想要變更的通訊帳號及類型
    private function resolveContact(Request $request): array
    {
        $newContact = $request->input('email') ?? $request->input('mobile');
        if (!$newContact) throw new Exception('請輸入欲變更的帳號');

        $contactType = $request->has('email') ? 'email' : ($request->has('mobile') ? 'mobile' : null);
        if (!$contactType) throw new Exception('Missing contact type');

        return [$newContact, $contactType];
    }

    // 查詢 UserContactUpdate 是否有符合資料 (用於輪詢查詢）
    private function searchPollingRecord(array $array): ?UserContactUpdate
    {
        return UserContactUpdate::userID($array['user']->id)
                                ->type($array['contact_type'])
                                ->newContact($array['new_contact'])
                                ->latest()
                                ->first();
    }

    // 檢查「聯絡帳號」是否已經更新
    private function isUpdate( UserContactUpdate $model, User $user ): ?bool
    {
        if (!$this->validationService->isMatched($user->id, $model->user_id)){
            throw new Exception("資料庫錯誤");
        } 

        if ($model->status !== 'completed') return false;
        
        $contactType = $model->contact_type;
        return ($user->$contactType === $model->new_contact);
    }

}