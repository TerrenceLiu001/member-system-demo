<?php

namespace App\Services;


use App\Models\UserContactUpdate;  
use App\Models\User;  
use Illuminate\Support\Facades\DB; 
use Illuminate\Http\Request;
use Exception;

/**
 * 通訊資料變更服務 (ContactUpdateService)
 * 
 * 處理會員變更聯絡資訊（目前支援 Email）的全流程邏輯，包括：
 * - 發起變更請求
 * - 寄送驗證信
 * - 驗證連結並執行更新
 * - 支援取消與輪詢查詢等流程
 *
 * 
 * UpdateContactController::updateEmail
 * └─ isRequestValid()
 * |  └─ validateContact() ← private
 * └─ prepareUpdateForEmail()
 *    └─ createAndUpdateRequest() ← private
 *
 * 
 * UpdateContactController::updateContact
 * └─ authorizeUpdateContactAccess()
 * 
 * 
 * UpdateContactController::buttonConfirm
 * └─ handdleConfirmation()
 *    ├─ checkStatus() ← private
 *    └─┬─ cancelUpdate ← private
 *      └─ completedUpdate ← private
 *         └─ updateConract ← MemberEditService
 * 
 * PollingStatusService::checkUpdateStatus
 * ├─ searchPollingRecord()
 * └─ isUpdate()
 *    
 * 
 * @used-by \App\Http\Controllers\UpdateContactController
 * @used-by \App\Services\Api\PollingStatusService
 * 
 * 使用模型與對應資料表：
 * - UserContactUpdate → member_center_contact_updates（聯絡資訊變更紀錄）
 * - User              → member_center_users           （正式會員資料表）
 */




class ContactUpdateService
{
    // 檢查「變更請求」是否有效，並回傳「聯絡通訊」類型
    public static function isRequestValid(Request $request): string
    {

        $user = $request->attributes->get('user');
        $type = ValidationService::checkContactType($request);

        $newContact = $request->$type;
        $currentContact = $user->$type;

        self::validateContact($newContact, $currentContact, $type);
        
        return $type;
    }

    // 準備寄送「電子郵件」
    public static function prepareUpdateForEmail(Request $request): void
    {
        $user = $request->attributes->get('user');
        $newEmail = $request->post('email');

        $token = self::createAndUpdateRequest($user, $newEmail, 'email');
        $link = MemberEmailService::generateLink('update_contact', [
            'email' => $user->email,
            'token' => $token,
        ]);

        MemberEmailService::sendUpdateContactEmail($newEmail, $user->email, $link);
    }

    // 是否有權限存取「變更確認」頁面
    public static function authorizeUpdateContactAccess(string $email, string $token): UserContactUpdate
    {
        $user = User::isRegistered($email);  
        if (!$user){
            throw new Exception("此變更要求並非來自會員，請先註冊");
        } 
        
        $record = MemberAuthService::verifyToken($token, 'update_contact', ['status' => 'pending']);
        if (!$record){
            throw new Exception('連結無效，請從新流程');
        }

        if (!ValidationService::isMatched($record->email, $email)){
            throw new Exception( "資料並不一致(email)，請重新流程" );
        } 
        if (!ValidationService::isMatched($user->id, $record->user_id)){
            throw new Exception( "資料並不一致(使用者 ID)，請重新流程" );
        } 

        return $record;
    }

    // 處理「變更/取消」的流程
    public static function handdleConfirmation(array $input): string
    {
        $email          = $input['email'] ?? null;
        $token          = $input['token'] ?? null;
        $contactType    = $input['contact_type'] ?? null;
        $action         = $input['action'] ?? null;

        $record = UserContactUpdate::email($email)->type($contactType)->latest('id')->first();
        self::checkStatus($record);

        $verifiedRecord = MemberAuthService::verifyToken($token, 'update_contact', ['status' => 'pending']);
        if (!ValidationService::isMatched($record->id, $verifiedRecord->id)) throw new Exception('驗證錯誤');

        return match ($action) 
        {
            'completed' => self::completedUpdate($record),
            'cancel'    => self::cancelUpdate($record),
            default     => throw new Exception("未知的操作指令"),
        };
    }

    // 查詢 UserContactUpdate 是否有符合資料 (用於輪詢查詢）
    public static function searchPollingRecord(array $array): ?UserContactUpdate
    {
        return UserContactUpdate::where('user_id', $array['user']->id)
                                ->where('contact_type', $array['contact_type'])
                                ->where('new_contact', $array['new_contact'])
                                ->latest()
                                ->first();
    }

    // 檢查「聯絡帳號」是否已經更新
    public static function isUpdate( UserContactUpdate $model, User $user ): ?bool
    {
        if (!ValidationService::isMatched($user->id, $model->user_id)){
            throw new Exception("資料庫錯誤");
        } 

        if ($model->status !== 'completed') return false;
        
        $contactType = $model->contact_type;
        return ($user->$contactType === $model->new_contact);
    }


    /** ----- 以下為私有方法 ----- */


    // 檢查欲變更的「聯絡帳號」是否和原本的相同
    private static function validateContact(string $newContact, string $currentContact, string $type): void
    {
        if (ValidationService::isMatched($newContact, $currentContact))
            {
            $label = ($type == 'email') ? '電子信箱' : '手機號碼';
            throw new Exception("變更的{$label}與目前相同");
        } 

        if (User::isRegistered($newContact, $type)){
            throw new Exception("此帳號已加入會員");
        } 
    } 


    // 在 UserUpdateContact 中「創建」並「更新」變更請求
    private static function createAndUpdateRequest(User $user, string $newData, string $contactType): string
    {
        return DB::transaction( function() use ($user, $newData, $contactType)
        {

            UserContactUpdate::userId($user->id)->type($contactType)->status('pending')->first()?->proceedTo('cancel'); 

            $result = UserContactUpdate::create([
                'user_id'       => $user->id,
                'email'         => $user->email,
                'mobile'        => $user->mobile,
                'contact_type'  => $contactType,
                'new_contact'   => $newData
            ]);

            $token = MemberAuthService::generateToken('update_contact');
            $result->updateTokenAndExpiry($token, 5);

            return $token;
        });
    }


    // 檢查 Status
    private static function checkStatus(UserContactUpdate $record): void
    {
        if (in_array($record->status,  ['completed', 'expired', 'cancel']))
        {
            $messages = [
                'completed' => "已完成變更，請勿重複操作",
                'expired'   => "流程已逾期，請重新操作",
                'cancel'    => "已取消變更，請勿重複操作",
            ];
            throw new Exception($messages[$record->status]);
        }
    } 

    // 「完成」變更
    private static function completedUpdate(UserContactUpdate $record): string
    {        
        MemberEditService::updateContact($record);
        $record->proceedTo('completed');
        return 'completed';
    }


    // 「取消」變更
    private static function cancelUpdate(UserContactUpdate $record): string
    {
        $record->proceedTo('cancel');
        return 'cancel';
    }


}