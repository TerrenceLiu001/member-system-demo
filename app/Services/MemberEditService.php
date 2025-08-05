<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserContactUpdate;  
use App\Repositories\Tokens\Implementations\EloquentUserRepository;
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
 * └─ handleEdit()
 *    ├─ ensureValid()              ← private
 *    │  └─ isMobileRegistered()    ← private
 *    ├─ formatMobile()             ← private
 *    └─ updateUserProfile()        ← private
 * 
 * UpdateContactService::finishConfirm
 * └─ updateContact()
 *    └─ editEmail() ← private
 * 
 * 
 * @used-by \App\Http\Controllers\MemberCenterController
 * @used-by \App\Services\UpdateContactService
 * 
 * 使用模型與對應資料表：
 * - User              → member_center_users           （正式會員資料表）
 * - UserContactUpdate  → member_center_contact_updates（聯絡資訊變更紀錄）
 */


class MemberEditService
{
    protected ValidationService $validationService;
    protected EloquentUserRepository $userRepository;

    public function __construct(
        ValidationService $validationService,
        EloquentUserRepository $userRepository
    )
    {
        $this->validationService = $validationService;
        $this->userRepository    = $userRepository;
    }

    // 執行「編輯」流程
    public function handleEdit(User $user, array $data): void
    {

        $this->ensureValid($user, $data);

        $data['mobile'] = $this->formatMobile($data['mobile']);

        $this->updateUserProfile($user, $data);
        
    } 

    // 更新 Contact 資訊
    public function updateContact(UserContactUpdate $model): void
    {
        $type = $model->contact_type;
        match ($type) 
        {
            'email'  => $this->editEmail($model),
            default  => throw new Exception( "不支援此類型的聯絡方式: {$type}"),
        };
    }

    /** ----- 以下為私有方法 ----- */



    // 檢查「資料格式」是否正確
    private function ensureValid(User $user, array $data): void
    {
        $this->validationService->validateEmail($data['email']);

        if ($data['username'] === null){
            throw new Exception("請輸入暱稱");
        }
        if (!$this->validationService->isMatched($user->email, $data['email'])){
            throw new Exception("請先驗證電子郵件");
        }
        if ($data['mobile'] !== null){
            $this->validationService->validateMobile($data['country'], $data['mobile']);
        } 
        if ($data['address'] !== null){
            $this->validationService->validateAddress($data['address']);
        } 
        $this->isMobileRegistered($data['mobile'], $user);
    }

    // 轉換手機格式 (補 0)
    private function formatMobile(?string $mobile): ?string
    {
        if (!$mobile) return null;
        return $mobile[0] === '0' ? $mobile : '0' . $mobile;
    }

    // 更新 User 的資料
    private function updateUserProfile(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) 
        {
            $this->userRepository->update($user, $data);
        });
    }

    // 檢查 Mobile 是否被註冊過
    private function isMobileRegistered(?string $mobile, User $user): void
    {
        if (!$mobile) return;

        if ($user->mobile && $this->validationService->isMatched(
            $mobile, $user->mobile
        )) return;

        if ($this->userRepository->findAccount($mobile, 'mobile')){
            throw new Exception("此手機號碼已經加入會員");
        }
    }

    // 編輯 Email 欄位
    private function editEmail(UserContactUpdate $model): void
    {
        $user = $this->userRepository->findAccount($model->email);

        if (!$user){
            throw new Exception("查無會員資料，請重新登入");
        }
        $data = [
            'email' => $model->new_contact
        ];

        $this->userRepository->update($user, $data);
    }


}
