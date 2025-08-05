<?php

namespace App\Services\Strategies\Verification\Implementations;

use App\Contracts\Model\Tokens\TokenStatusInterface;
use App\Services\ServiceRegistry;
use App\Services\Strategies\Verification\AbstractVerificationStrategy;
use App\Repositories\Tokens\Implementations\EloquentContactUpdateRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * 變更聯絡資料流程策略 (UpdateContactVerificationStrategy)
 *
 * - 實作變更 Email  流程的具體邏輯：
 *   - 驗證使用者與輸入資料
 *   - 建立或更新變更聯絡資料記錄
 *   - 寄送驗證信件至新聯絡資訊
 * 
 * 
 * 
 * 
 * Orchestrator::verificationFlow()
 * ├─ validateAndPrepareRequest()
 * |  └─ ensureContactValid()       ← private
 * ├─ createAndUpdateRecord()
 * ├─ getLinkInfo()
 * └─ dispatchVerificationEmail()
 * 
 * 
 * 
 * @used-by \App\Services\Strategies\Verification\VerificationEmailOrchestrator
 *
 * 使用模型與對應資料表：
 * - UserContactUpdate → member_center_user_contact_updates
 */


class UpdateContactVerificationStrategy extends AbstractVerificationStrategy
{

    protected EloquentContactUpdateRepository $contactRepository;

    public function __construct(
        ServiceRegistry $services,
        EloquentContactUpdateRepository $contactRepository)
    {
        parent::__construct($services);
        $this->contactRepository = $contactRepository;
    }


    // 驗證 Request 是否有效，並返回資料
    public function validateAndPrepareRequest(Request $request): mixed
    {

        $user = $request->attributes->get('user') ?? throw new Exception("請重新登入");
        $contactType = $this->services->validationService->checkContactType($request);

        $newContact = $request->$contactType;
        $currentContact = $user->$contactType;

        $this->ensureContactValid($newContact, $currentContact, $contactType);
        return [
            'user'         => $user, 
            'new_contact'  => $newContact , 
            'contact_type' => $contactType
        ];

    }

    // 創建或更新資料庫的紀錄
    public function createAndUpdateRecord(mixed $data): TokenStatusInterface
    {
        [
            'user'         => $user, 
            'new_contact'  => $newContact ,
            'contact_type' => $contactType
        ] = $data;

        return DB::transaction( function () use ($user, $newContact, $contactType){

            $this->contactRepository->cancelPending([
                'user_id' => $user->id,
                'contact_type' => $contactType
            ]);

            $record = $this->contactRepository->create([
                'user_id'       => $user->id,
                'email'         => $user->email,
                'mobile'        => $user->mobile,
                'contact_type'  => $contactType,
                'new_contact'   => $newContact
            ]);

            $token = $this->services->memberAuthService->generateToken('update_contact');
            $this->contactRepository->handleToken(
                $record, $token, 5
            );

            return  $record;
        });
    }
    
    // 寄送「變更通訊」信件
    public function dispatchVerificationEmail(TokenStatusInterface $record, string $verificationLink): void
    {
        $this->services->memberEmailService->sendUpdateContactEmail(
            $record->new_contact, 
            $record->email,       
            $verificationLink
        );
    }


    // 準備「連結」參數
    public function getLinkInfo(mixed $record): array
    {
        return [
            'routeName' => 'update_contact',
            'params'    => [
                'email' => $record->email, 
                'token' => $record->update_contact_token
        ]];
    }

    /** ----- 以下為私有方法 ----- */


    // 檢查欲變更的「聯絡帳號」是否有效
    private function ensureContactValid(string $newContact, string $currentContact, string $type): void
    {
        if ($this->services->validationService->isMatched(
            $newContact, $currentContact
        )){
            $label = ($type == 'email') ? '電子信箱' : '手機號碼';
            throw new Exception("變更的{$label}與目前相同");
        } 


        if ($this->services->userRepository->findAccount(
            $newContact, $type
        )){
            throw new Exception("此帳號已加入會員");
        } 
    } 
    
}