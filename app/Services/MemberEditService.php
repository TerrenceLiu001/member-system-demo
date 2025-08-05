<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserContactUpdate;  
use App\Models\User;
use Exception;

/**
 * 會員資料編輯服務 (MemberEditService)
 * 
 * - 驗證「資料編輯」的請求是否有效
 * - 更新會員主要資料欄位（姓名、性別、年齡層、手機、地址等）
 * - 更新會員聯絡資訊
 * 
 * 
 * MemberCenterController::editMemberData
 * ├─ isRequestValid()
 * └─ updateMemberData()
 * 
 * ContactUpdateService::finishConfirm
 * └─ updateContact()
 *    └─ editEmail() ← private
 * 
 * 
 * @used-by \App\Http\Controllers\MemberCenterController
 * @used-by \App\Services\ContactUpdateService
 * 
 * 使用模型與對應資料表：
 * - User              → member_center_users           （正式會員資料表）
 * - UserContactUpdate  → member_center_contact_updates（聯絡資訊變更紀錄）
 */


class MemberEditService
{

    // 檢查「編輯請求」是否有效
    public static function isRequestValid(User $user, Request $request): void
    {
        ValidationService::validateEmail($request->email);
        if ($request->mobile !== null) ValidationService::validateMobile($request->country, $request->mobile);
        if ($request->address !== null) ValidationService::validateAddress($request->address);

        if ($request->username === null) throw new Exception("請輸入暱稱");
        if (!ValidationService::isMatched($user->email, $request->email)) throw new Exception("請先驗證電子郵件");
        self::isMobileRegistered($request->mobile, $user);

    }

    // 更新 User 的資料
    public static function updateMemberData(User $user, Request $request): ?DB
    {
        $mobile = $request->mobile;
        if ($mobile && substr($mobile,0,1) !== '0') $mobile = '0'.$mobile;

        return DB::transaction(function () use ($user, $request, $mobile) 
        {
            $user->update([
                'username'  => $request->username,
                'gender'    => $request->gender,
                'age_group' => $request->age_group,
                'address'   => $request->address,
                'country'   => $request->country,
                'mobile'    => $mobile,
            ]);
        });
    }

    // 更新 Contact 資訊
    public static function updateContact(UserContactUpdate $model): void
    {
        if ($model->isRequestCompleted()) throw new Exception( "請勿重複操作，變更流程已經結束" );
        
        $type = $model->contact_type;
        match ($type) 
        {
            'email'  => self::editEmail($model),
            default  => throw new Exception( "不支援此類型的聯絡方式: {$type}"),
        };
    }

    // 編輯 Email 欄位
    private static function editEmail(UserContactUpdate $model): void
    {
        $user = User::isRegistered($model->email);
        if (!$user) throw new Exception(" 查詢不到會員資料，請重新登入");

        $user->update(['email' => $model->new_contact]);
    }

    // 檢查 Mobile 是否被註冊過
    private static function isMobileRegistered(?string $mobile, User $user): void
    {
        if (!$mobile) return;
        if ($user->mobile && ValidationService::isMatched($mobile, $user->mobile)) return;
        if (User::isRegistered($mobile, 'mobile')) throw new Exception("此手機號碼已經加入會員");
    }

}
