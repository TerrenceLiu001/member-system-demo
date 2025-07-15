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
 *    ├─ createAndUpdateRequest() ← private
 *    ├─ generateLink() ← MemberEmailService
 *    └─ sendUpdateContactEmail() ← MemberEmailService
 *
 * 
 * UpdateContactController::updateContact
 * └─ authorizeUpdateContactAccess()
 *    ├─ verifyUpdateContactToken() ← private
 *    ├─ isMatched() ← ValidationService
 *    └─ isRegistered() ← User
 * 
 * 
 * UpdateContactController::updateConfirm
 * └─ finishConfirm()
 *    ├─ verifyUpdateContactToken() ← private
 *    ├─ updateContact() ← MemberEditService
 *    └─ markVerification() ← Model method
 * 
 * 
 * UpdateContactController::cancelConfirm
 * └─ cancelRequest()
 *    └─ verifyUpdateContactToken() ← private
 *
 * 
 * PollingStatusService::checkUpdateStatus
 * ├─ searchPollingRecord()
 * └─ isUpdate()
 *    ├─ assertStatusConsistent() ← private
 *    └─ isMatched() ← ValidationService
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
        
        $record = self::verifyUpdateContactToken($token);
        if (!$record){
            throw new Exception("連結無效，請重新流程");
        } 

        if (!ValidationService::isMatched($record->email, $email)){
            throw new Exception( "資料並不一致(email)，請重新流程" );
        } 
        if (!ValidationService::isMatched($user->id, $record->user_id)){
            throw new Exception( "資料並不一致(使用者 ID)，請重新流程" );
        } 

        return $record;
    }

    // 完成變更流程
    public static function finishConfirm(?string $token): void
    {

        $record = self::verifyUpdateContactToken($token);
        if (!$record) throw new Exception("查詢不到「變更」請求，請重新開始");
        
        MemberEditService::updateContact($record);
        $record->markVerification();
    }

    // 取消變更請求，將紀錄中的 「status」 改成 「cancel」
    public static function cancelRequest(?string $token): void
    {
        $record = self::verifyUpdateContactToken($token);
        if (!$record) throw new Exception("查詢不到「變更」請求，請重新開始");

        if (in_array($record->status, ['verified', 'expired', 'cancel']))
        {
            $messages = [
                'verified' => "已完成變更，請勿重複操作",
                'expired'  => "流程已逾期，請重新操作",
                'cancel'   => "已取消變更，請勿重複操作",
            ];
            throw new Exception($messages[$record->status]);
        }
        $record->status = 'cancel';
        $record->save();
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

        self::assertStatusConsistent($model);
        if (!$model->verification_at) return false;
        
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
            UserContactUpdate::where('user_id', $user->id)
                             ->where('contact_type', $contactType)
                             ->where('status', 'pending')
                             ->update(['status' => 'cancel']);

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

    // 驗證「Update Contact Token」
    private static function verifyUpdateContactToken(?string $token): ?UserContactUpdate
    {
        $record = UserContactUpdate::where('update_contact_token', $token)
                                   ->where('status', 'pending')->first();

        if (!$record) return null;
        if (!MemberAuthService::isTokenValid($record))
        {
            $record->status = 'expired';
            $record->save();
            throw new Exception("驗證流程已過期，請從新開始");
        }
        return $record;

    }

    // 檢查資料庫是否存在矛盾
    private static function assertStatusConsistent(UserContactUpdate $model): void
    {
        $status = $model->status;
        $verified = $model->verification_at !== null;

        if (($status === 'verified') !== $verified)
            throw new Exception("驗證請求記錄狀態異常，請檢查資料庫");
    }
}