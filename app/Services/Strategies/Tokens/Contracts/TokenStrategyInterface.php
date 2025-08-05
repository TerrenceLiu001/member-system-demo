<?php

namespace App\Services\Strategies\Tokens\Contracts;

use App\Models\Base\BaseTokenModel;

interface TokenStrategyInterface
{
    /**
     * 解析並返回與 Token 相關的模型
     * @param string $token
     * @param array  $scopes
     * @return BaseTokenModel|null
     */
    public function resolveModel(string $token, array $scopes = []): ? BaseTokenModel;

    /**
     * 檢查模型 Token 是否過期。
     * @param TokenCapableInterface $model
     * @return bool
     */
    public function isExpired(BaseTokenModel $model): bool;

    /**
     * 處理過期的 Token。
     * @param TokenCapableInterface $model
     * @return void
     */
    public function handleExpired(BaseTokenModel $model): void;

    /**
     * 返回 Token 過期時的錯誤訊息。
     * @return string
     */
    public function getExpiredMessage(): string;

    /**
     * 返回 Token 無效時的錯誤訊息。
     * @return string
     */
    public function getInvalidMessage(): string;

    /**
     * 生成一個新的唯一 Token 並返回。
     * @param int|null $length Token 長度
     * @return string
     */
    public function generateToken(?int $length = 64): string;
}