<?php 

namespace App\Services\Strategies\Verification\Implementations;

use App\Contracts\Model\Tokens\TokenStatusInterface;
use App\Services\ServiceRegistry;
use App\Services\Strategies\Verification\AbstractVerificationStrategy;
use App\Repositories\Tokens\Implementations\EloquentPasswordUpdateRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;


/**
 * 忘記密碼流程策略 (ForgotPasswordVerificationStrategy)
 *
 * - 處理忘記密碼相關的流程，包含：
 *   - 驗證輸入信箱是否為會員
 *   - 建立對應的密碼重設紀錄
 *   - 寄送重設密碼郵件
 * 
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
 * 
 * 使用模型與對應資料表：
 * - PasswordUpdate  → member_center_password_updates
 */



class ForgotPasswordVerificationStrategy extends AbstractVerificationStrategy
{

    protected EloquentPasswordUpdateRepository $passwordRepository;

    public function __construct(
        ServiceRegistry $services,
        EloquentPasswordUpdateRepository $passwordRepository)
    {
        parent::__construct($services);
        $this->passwordRepository = $passwordRepository;
    }
    
    public function getType(): string 
    {
        return 'forgot_password';
    }

    // 驗證 Request 是否有效，並返回資料
    public function validateAndPrepareRequest(Request $request): mixed
    {   
        $email = $request->input('email') ?? throw new Exception('請輸入電子信箱');

        $this->services->validationService->validateEmail($email);

        $user = $this->services->userRepository->findAccount($email);
        if(!$user){
            throw new Exception('此信箱並非會員，請先註冊');
        }

        return [
            'email' => $email, 
            'user'  => $user
        ];
    }


    // 創建並更新 Update Password 的紀錄
    public function createAndUpdateRecord(mixed $data): TokenStatusInterface
    {
        [
            'email' => $email,
            'user' => $user
        ] = $data;

        return DB::transaction( function () use ($user, $email){

            $this->passwordRepository->cancelPending([
                'user_id' => $user->id,
                'email'   => $email
            ]);

            $record = $this->passwordRepository->create([
                'user_id'  => $user->id,
                'email'    => $email,
                'type'     => 'forgot', 
                'status'   => 'pending' 
            ]);

            $token = $this->services->memberAuthService->generateToken('password');
            $this->passwordRepository->handlePasswordToken(
                $record, 
                $token
            );

            return $record;
        });
    }


    // 寄送「忘記密碼」信件
    public function dispatchVerificationEmail(TokenStatusInterface $record, string $verificationLink): void
    {
        $this->services->memberEmailService->sendResetPasswordEmail(
            $record->email, 
            $verificationLink
        );
    }

    // 準備「連結」參數
    public function getLinkInfo(TokenStatusInterface $record): array
    {
        return [
            'routeName' => 'reset_password',
            'params'    => [
                'email' => $record->email, 
                'token' => $record->password_token
        ]];
    }

}