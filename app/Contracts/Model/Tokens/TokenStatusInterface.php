<?php

namespace App\Contracts\Model\Tokens;

use Illuminate\Database\Eloquent\Builder;


/**
 * 定義 Model 的介面：
 * 繼承 TokenCapableInterface，定義了 Model 具備處理 Status 的能力
 */
interface TokenStatusInterface extends TokenCapableInterface
{
 
    /**
     * 依據 Token 的狀態進行查詢。
     *
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeStatus(Builder $query, string $status): Builder;

    /**
     * 將 Token 的狀態轉換為指定的狀態。
     *
     * @param string $status 新的狀態（例如: 'completed', 'cancelled'）。
     * @return static 狀態轉換後的物件本身，用於鏈式呼叫
     */
    public function proceedTo(string $status): static;
}
