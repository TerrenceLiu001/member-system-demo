<?php

namespace App\Services\UpdateContact;

use App\Models\User;
use App\Models\UserContactUpdate;
use App\Services\ServiceRegistry;
use App\Services\AbstractUnitService;
use App\Repositories\Tokens\Implementations\EloquentContactUpdateRepository;
use Exception;

/**
 * 通訊資料變更單元服務 (UnitUpdateContactService)
 *
 * - 專注於執行通訊資料變更流程中「最小單元」的邏輯。
 * - 包含帳號查找、Token 驗證、資料確認與實際更新等原子性操作。
 *
 *   對應上一層 Service 的流程如下：
 *
 * UpdateContactService::authorizeUpdateContactPage()
 * ├─ findUserByAccount()           
 * ├─ verifyUpdateContactToken()     
 * └─ ensureRecordMatchesUser()     
 * 
 * UpdateContactService::handleConfirmation()
 * ├─ ensureDataValid()                     
 * └─ completedUpdate() or cancelUpdate()    
 *
 * @used-by \App\Services\UpdateContactService
 *
 */



class UnitUpdateContactService extends AbstractUnitService
{
    protected EloquentContactUpdateRepository $contactRepository;

    public function __construct(
        ServiceRegistry $services,
        EloquentContactUpdateRepository $contactRepository
    )
    {
        parent::__construct($services);
        $this->contactRepository = $contactRepository;
    }


    // 在 User Table 找會員資料
    public function findUserByAccount(string $email): User
    {
        $user = $this->services->userRepository->findAccount($email);
        if (!$user){
            throw new Exception("此變更要求並非來自會員，請先註冊");
        }
        return $user;           
    }

    // 驗證 Update Contact Token
    public function verifyUpdateContactToken(string $token): UserContactUpdate
    {
        $record = $this->services->memberAuthService->verifyToken(
            $token, 
            'update_contact', 
            ['status' => 'pending']
        );

        if (!$record){
            throw new Exception('連結無效，請從新流程');
        }
        
        return $record;
    } 

    // 檢查資料是否一致
    public function ensureRecordMatchesUser(User $user, UserContactUpdate $record, string $email): void
    {
        if (!$this->services->validationService->isMatched(
            $record->email, $email
        )){
            throw new Exception( "Email 資料不一致，請重新流程" );
        } 

        if (!$this->services->validationService->isMatched(
            $record->user_id, $user->id
        )){
            throw new Exception( "使用者 ID 資料不一致，請重新流程" );
        } 
    }

    // 檢查資料
    public function ensureDataValid($data): array
    {
        [
            'email' => $email, 
            'token' => $token, 
            'contact_type' => $contactType, 
            'action' => $action
        ] = $data;


        $record = $this->contactRepository->findByEmailAndContactType(
            $email, 
            $contactType
        );

        $this->checkStatus($record);

        $verifiedRecord = $this->services->memberAuthService->verifyToken(
            $token, 
            'update_contact', 
            ['status' => 'pending']
        );
        
        if (!$this->services->validationService->isMatched(
            $record->id, 
            $verifiedRecord->id
        )){
            throw new Exception('驗證錯誤');
        }

        return [
            'record' => $record, 
            'action' => $action
        ];
    }


    // 「完成」變更
    public function completedUpdate(UserContactUpdate $record): void
    {        
        $this->services->memberEditService->updateContact($record);
        $this->contactRepository->markStatus($record, 'completed');
    }


    // 「取消」變更
    public function cancelUpdate(UserContactUpdate $record): void
    {
        $this->contactRepository->markStatus($record, 'cancel');
    }


    // 檢查 Status
    private function checkStatus(UserContactUpdate $record): void
    {
        if (in_array(
            $record->status,  ['completed', 'expired', 'cancel']
        ))
        {
            $messages = [
                'completed' => "已完成變更，請勿重複操作",
                'expired'   => "流程已逾期，請重新操作",
                'cancel'    => "已取消變更，請勿重複操作",
            ];
            throw new Exception($messages[$record->status]);
        }
    }

}