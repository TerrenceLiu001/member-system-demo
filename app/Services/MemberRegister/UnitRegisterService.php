<?php

namespace App\Services\MemberRegister;

use App\Models\User;
use App\Services\ServiceRegistry;
use App\Services\AbstractUnitService;
use App\Repositories\Tokens\Implementations\EloquentGuestRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * 會員單元註冊服務 (UnitRegisterService)
 *
 * - 專注於執行會員註冊流程中「最小單元」的邏輯。
 * - 包含資料驗證、Token 驗證、會員建立與 Cookie 設定等原子性操作。
 * 
 *   對應上一層 Service 的流程如下：
 *  
 *  MemberRegisterService::authorizeSetPasswordPage
 *  ├─ ensureAccountValid()   
 *  └─ verifyRegisterToken()  
 *
 *  MemberRegisterService::completeRegistration
 *  ├─ ensureDataValid()  
 *  ├─ createMember()     
 *  └─ setCookie()        
 *
 * @used-by \App\Services\MemberRegisterService
 */

class UnitRegisterService extends AbstractUnitService
{
    protected EloquentGuestRepository $guestRepository;

    public function __construct(
        ServiceRegistry $services,
        EloquentGuestRepository $guestRepository
    )
    {
        parent::__construct($services);
        $this->guestRepository = $guestRepository;
    }

 // 驗證並準備註冊頁面的資料
    public function ensureDataValid(?string $email, ?string $password, ?string $confirmed): array
    {

        $this->ensureAccountValid($email);

        $guest = $this->guestRepository->findPendingRecord([
            'email' => $email
        ]);

        $this->services->memberAuthService->verifyToken(
            $guest, 
            'register'
        );

        $this->services->validationService->checkPasswordInputs(
            $password, 
            $confirmed
        );

        return [ 
            'email' => $email, 
            'password' => $password, 
            'guest' => $guest
        ];
    }
    
    // 建立新會員
    public function createMember(array $data): User
    {
        [
            'email'    => $email, 
            'password' => $password, 
            'guest'    => $guest
        ] = $data;
        
        return DB::transaction(function() use ($email, $password, $guest) {

            // 將 Guests 中的 Record 標記為 completed
            $this->guestRepository->markStatus($guest, 'completed');

            // 在 Users 中建立 Record
            $user = $this->services->userRepository->create([
                'email'       => $email,
                'password'    => bcrypt($password),
                'guest_id'    => $guest->id,
            ]);

            // 在 User 中生成 Bearer Token 
            $bearerToken = $this->services->memberAuthService->generateToken('login');
            $this->services->userRepository->handleToken(
                $user, $bearerToken, 1440
            );

            return $user;
        });
    }

    // 檢查帳號是否有效
    public function ensureAccountValid(string $email): void
    {
        $this->services->validationService->validateEmail($email);
        if ($this->services->userRepository->findAccount($email)) {
            throw new Exception('此信箱已被註冊，請直接登入');
        }
    }

    // 驗證 Register Token
    public function verifyRegisterToken(string $token, string $email): void
    {
        $guest = $this->services->memberAuthService->verifyToken(
            $token,
            'register'
        );

        if (!$guest || $guest->email !== $email) {
            throw new Exception("無效連結，請重新註冊");
        }
    }

    // 設定 Cookie
    public function setCookie(string $bearerToken): Cookie
    {
        return $this->services->memberAuthService->setBearerTokenCookie(
            $bearerToken, 
            2880
        );
    }

}