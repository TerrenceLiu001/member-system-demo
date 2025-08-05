<?php

namespace App\Services\ForgotPassword;

use App\Models\User;
use App\Services\ServiceRegistry;
use App\Services\AbstractUnitService;
use App\Repositories\Tokens\Implementations\EloquentPasswordUpdateRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class UnitForgotPasswordService extends AbstractUnitService
{
    protected EloquentPasswordUpdateRepository $passwordRepository;

    public function __construct(
        ServiceRegistry $services,
        EloquentPasswordUpdateRepository $passwordRepository
    )
    {
        parent::__construct($services);
        $this->passwordRepository = $passwordRepository;
    }



    // 確認 Request 是否有效
    public function ensureAccountValid(string $email): User
    {
        $this->services->validationService->validateEmail($email);
        $user = $this->services->userRepository->findAccount($email); 

        if(!$user){
            throw new Exception('此信箱並非會員，請先註冊');
        };

        return $user;
    }

    // 驗證「設定密碼」頁面提交的資料是否有效
    public function ensureDataValid(?string $token, ?string $password, ?string $confirmed): array
    {

        $scopes = [   
            'status' => 'pending', 
            'type' => 'forgot'
        ];

        $record = $this->services->memberAuthService->verifyToken(
            $token, 
            'password', 
            $scopes
        );

        $this->services->validationService->checkPasswordInputs(
            $password, 
            $confirmed
        );

        return [
            'password' => $password, 
            'record' => $record
        ];
    }

    // 完成「重設密碼」
    public function resetPassword(array $data): void
    {
        [
            'password' => $password, 
            'record'   => $record
        ] = $data;

        DB::transaction(function () use ($password, $record) {

            // 將 PasswordUpdate 中的 Record 標記為 completed
            $this->passwordRepository->markStatus($record, 'completed');

            // 將 Users 中的密碼更新
            $user = $this->services->userRepository->resetPassword(
                $record->user_id, 
                bcrypt($password)
            );

            // 將 User 中的 Bearer Token 更新
            $bearerToken = $this->services->memberAuthService->generateToken('login');
            $this->services->userRepository->handleToken(
                $user, $bearerToken, 1440
            );
        });
    }

    // 驗證 Password Token
    public function verifyPasswordToken(string $token, string $email): void
    {
        $scopes = [
            'status' => 'pending', 
            'type' => 'forgot'
        ];

        $record = $this->services->memberAuthService->verifyToken(
            $token, 
            'password', 
            $scopes
        );

        if (!$record || $record->email !== $email) {
            throw new Exception("無效的重設密碼連結");
        }
    }
}