<?php

namespace App\Services;

use App\Mail\RegisterVerificationEmail;
use App\Mail\UpdateVerificationEmail;
use App\Mail\PasswordVerificationEmail;
use Illuminate\Support\Facades\Mail;

class MemberEmailService
{

    // 寄送「開通註冊」的信件
    public function sendRegisterEmail(string $email, string $link): void
    {
        Mail::to($email)->send(new RegisterVerificationEmail($link, $email));
    }

    // 寄送「變更通訊」的信件
    public function sendUpdateContactEmail(string $newEmail,string $currentEmail, string $link): void
    {
        Mail::to($newEmail)->send(new UpdateVerificationEmail($newEmail, $currentEmail, $link));
    }

    // 寄送「忘記密碼」的信件
    public function sendResetPasswordEmail(string $email, string $link): void
    {
        Mail::to($email)->send(new PasswordVerificationEmail($link, $email));
    }

    // 生成 Email 中的驗證連結
    public function generateLink(string $routeName, array $params): string
    {
        return route($routeName, $params);
    }

}