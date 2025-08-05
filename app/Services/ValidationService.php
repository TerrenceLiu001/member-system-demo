<?php
namespace App\Services;

use Exception;
use Illuminate\Http\Request;

class ValidationService
{
    // 驗證電子郵件格式
    public static function validateEmail(?string $email): void
    {
        if(empty($email)){
            throw new Exception('請輸入電子信箱');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false){
            throw new Exception("電子信箱格式錯誤");       
        }
    }

    // 驗證密碼格式
    public static function validateMobile(?string $country, ?string $mobile): void
    {
        if(empty($country) || empty($mobile)) throw new Exception("請選擇國家代碼，並輸入手機號碼");

        $isValid = match ($country) 
        {
            'TWN' => preg_match('/^(0?9\d{8})$/', $mobile), 
            'KOR'  => preg_match('/^0?(70|80|90)\d{8}$/', $mobile), 
            'JPN'  => preg_match('/^(0?1[016789]\d{8})$/', $mobile), 
            default => throw new Exception("沒有匹配的國家代碼"),
        };

        if (!$isValid) throw new Exception('手機號碼格式不對');
    }

    // 驗證地址是否含有非法字元
    public static function validateAddress(?string $address): void
    {
        $pattern = '/^[\p{L}0-9\s.\-]+$/u';
        if (!preg_match($pattern, $address)) throw new Exception("請輸入有效地址");
    }

    // 檢查「設定/重設密碼」的輸入是否正確
    public static function checkPasswordInputs(?string $password, ?string $confirmed): void
    {
        if (empty($password) || empty($confirmed)) {
            throw new Exception('請確實輸入新密碼');
        }

        if (!self::validatePassword($password)) {
            throw new Exception('密碼格式不對');
        }

        if (!self::isMatched($password, $confirmed)) {
            throw new Exception('密碼並不一致');
        }
    }

    // 驗證密碼格式
    public static function validatePassword(string $password): bool
    {
        return  preg_match('/[A-Z]/', $password) &&  
                preg_match('/[a-z]/', $password) &&
                preg_match('/\d/',    $password) &&  
                preg_match('/^.{8,}$/', $password);
    }

    // 檢查是否相等
    public static function isMatched(string $data_1, string $data_2): bool
    {
        return ($data_1 === $data_2);
    }

    // 檢查「通訊帳號」為電子郵件或手機
    public static function checkContactType(Request $request): ?string
    {
        $contactType = ($request->has('email'))
            ? 'email' : (($request->has('mobile')) 
                ? 'mobile' : throw new Exception("通訊類別不正確")); 

        ($contactType == 'email')
            ? self::validateEmail($request->$contactType)
            : self::validateMobile($request->country, $request->$contactType);

        return $contactType;
    }


}
