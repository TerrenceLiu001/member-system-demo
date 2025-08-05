<?php
namespace App\Contracts\Model\Tokens;


/**
 * 定義 Model 的介面：
 * 實作以下方法的 Model 即可視為具備處理 Token 的能力
 */

interface TokenCapableInterface
{
    /**
     * 取得與此物件相關聯的 Token 欄位名稱。
     *
     * @return string Token 欄位的名稱（e.g.  'bearer_token'）。
     */
    public function getTokenName(): string;


    /**
     * 取得 Token 的過期時間。
     *
     * @return \DateTimeInterface|null Token 過期時間，如果沒有則為 null。
     */
    public function getTokenExpiresAt(): ?\DateTimeInterface;


    /**
     * 更新物件的 Token 欄位與過期時間。
     *
     * @param string $token 新的 Token 。
     * @param int|null $minutes Token 的有效期限，單位為分鐘。
     * 
     * @return static 回傳自身實例，便於鏈式操作
     */
    public function updateTokenAndExpiry(string $token, ?int $minutes = 10): static;

}
