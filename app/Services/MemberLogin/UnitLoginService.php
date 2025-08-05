<?php

namespace App\Services\MemberLogin;

use App\Models\User;
use App\Services\ValidationService;
use App\Services\MemberAuthService;
use App\Repositories\Tokens\Implementations\EloquentUserRepository;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;
use Exception;


class UnitLoginService
{

    protected MemberAuthService $memberAuthService;
    protected ValidationService $validationService;
    protected EloquentUserRepository $userRepository;

    public function __construct(
        MemberAuthService $memberAuthService,
        ValidationService $validationService,
        EloquentUserRepository $userRepository
    )
    {
        $this->memberAuthService = $memberAuthService;
        $this->validationService = $validationService;
        $this->userRepository    = $userRepository;
    }



    // 檢查登入類型
    public function checkLoginType(?string $phoneCode): string
    {
        return match ($phoneCode) 
        {
            '+886'  => 'TWN',
            '+82'   => 'KOR', 
            '+81'   => 'JPN', 
            default => 'email',
        };
    } 

    // 確認請求是否有效
    public function validateInput(string $account, string $password, string $type): void
    {
        if (!$account) throw new Exception("請輸入帳號");
        if (!$password) throw new Exception("請輸入密碼");

        match ($type) 
        {
            'email'   => $this->validationService->validateEmail($account),
             default  => $this->validationService->validateMobile($type, $account),
        };
    }


    // 檢查「帳號」、「密碼」是否一致
    public function verifyCredentials(string $account, string $password, string $type): ?User
    {
        $user = $this->userRepository->findAccount($account, $type);
        if (!$user){
            throw new Exception("尚未加入會員，請先註冊");
        } 

        // 當帳號為 mobile 時才要檢查
        if ($type !== 'email' && $user->country !== $type){
            throw new Exception("手機區碼與資料庫不符");
        } 

        return (Hash::check($password, $user->password))? $user : null;
    }

    // 設定「登入」狀態
    public function setLogin(User $user): Cookie
    {
        $bearerToken = $this->memberAuthService->generateToken('login');
        $this->userRepository->handleToken(
            $user, $bearerToken, 1440
        );

        $cookie = $this->memberAuthService->setBearerTokenCookie(
                $user->bearer_token, 2880
            );

        return $cookie;
    }

    // 設定「登出」狀態
    public function setLogout(User $user): Cookie
    {
        $this->userRepository->cleanBearerToken($user);     
        $cookie = $this->memberAuthService->forgetBearerToken();

        return $cookie;
    }
}