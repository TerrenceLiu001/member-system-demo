<?php

namespace App\Services\Strategies\Verification\Implementations;

use App\Contracts\Model\Tokens\TokenStatusInterface;
use App\Services\ServiceRegistry;
use App\Services\Strategies\Verification\AbstractVerificationStrategy;
use App\Repositories\Tokens\Implementations\EloquentGuestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * 註冊流程策略 (RegisterVerificationStrategy)
 *
 * - 實作註冊相關流程的具體邏輯：
 *   - 驗證請求並準備資料
 *   - 建立和更新 Guest 紀錄
 *   - 寄送驗證信
 * 
 * 
 * Orchestrator::verificationFlow()
 * ├─ validateAndPrepareRequest()
 * ├─ createAndUpdateRecord()
 * ├─ getLinkInfo()
 * └─ dispatchVerificationEmail()
 * 
 * 
 * 
 * @used-by \App\Services\Strategies\Verification\VerificationEmailOrchestrator
 *
 * 使用模型與對應資料表：
 * - Guest → member_center_guests
 * - User  → member_center_users
 */




class RegisterVerificationStrategy extends AbstractVerificationStrategy
{
    
    protected EloquentGuestRepository $guestRepository;

    public function __construct(
        ServiceRegistry $services,
        EloquentGuestRepository $guestRepository)
    {
        parent::__construct($services);
        $this->guestRepository = $guestRepository;
    }

    public function getType(): string 
    {
        return 'register';
    }

    // 驗證 Request 是否有效，並返回資料
    public function validateAndPrepareRequest(Request $request): mixed
    {   
        $email = $request->input('account') ?? throw new Exception('請輸入帳號');

        $this->services->validationService->validateEmail($email);

        if ($this->services->userRepository->findAccount($email)) {
            throw new Exception('此信箱已被註冊，請直接登入');
        }

        return ['email' => $email];
    }

    // 創建並更新 Guest 的紀錄
    public function createAndUpdateRecord(mixed $data): TokenStatusInterface
    {
        return DB::transaction(function () use ($data) {

            // 將之前的 Record 的 Status 設為 cancel
            $this->guestRepository->cancelPending([
                'email' => $data['email']
            ]);
           
            // 創建新的 Record
            $guest = $this->guestRepository->create([
                'email'  => $data['email'],
                'status' => 'pending'
            ]);

            $token = $this->services->memberAuthService->generateToken('register');
            $this->guestRepository->handleToken(
                $guest, $token, 10
            ); 

            return $guest;
        });
    }

    // 寄送註冊信件
    public function dispatchVerificationEmail(TokenStatusInterface $record, string $verificationLink): void
    {
        $this->services->memberEmailService->sendRegisterEmail(
            $record->email, 
            $verificationLink
        );
    }

    // 準備「連結」參數
    public function getLinkInfo(TokenStatusInterface $record): array
    {
        return [
            'routeName' => 'set_password',
            'params'    => [
                'email' => $record->email, 
                'token' => $record->register_token
        ]];
    }
}