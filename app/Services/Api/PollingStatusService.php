<?php

namespace App\Services\Api;

use App\Models\User;
use App\Services\MemberAuthService;
use App\Services\ContactUpdateService;
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
 *     └─ 呼叫 ContactUpdateService 相關方法
 * 
 * 
 * @used-by \App\Http\Controllers\PollingStatusController
 */


class PollingStatusService
{
    // 驗證請求，並取得使用者物件
    public static function isRequestValid(Request $request): array
    {
        $user = MemberAuthService::verifyToken($request->bearerToken(), 'login');
        if(!$user) throw new Exception('身份驗證失敗，請重新登入');

        [$contact, $type] = self::resolveContact($request);
        
        return ['user' => $user, 'new_contact' => $contact, 'contact_type' => $type];
    }


    // 查詢「變更通訊」紀錄的 Status
    public static function checkUpdateStatus(array $array): array
    {
        $record = ContactUpdateService::searchPollingRecord($array);
        if (!$record) throw new Exception("操作錯誤，請重新點擊「驗證」按鈕");

        $isUpdated = ContactUpdateService::isUpdate($record, $array['user']);
        return [ 'is_updated' => $isUpdated, 'status' => $record->status ];
    }


    /** ----- 以下為私有方法 ----- */


    // 提取想要變更的通訊帳號及類型
    private static function resolveContact(Request $request): array
    {
        $newContact = $request->input('email') ?? $request->input('mobile');
        if (!$newContact) throw new Exception('請輸入欲變更的帳號');

        $contactType = $request->has('email') ? 'email' : ($request->has('mobile') ? 'mobile' : null);
        if (!$contactType) throw new Exception('Missing contact type');

        return [$newContact, $contactType];
    }
}