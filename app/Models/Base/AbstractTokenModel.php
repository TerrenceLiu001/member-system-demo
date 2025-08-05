<?php

namespace App\Models\Base;

use App\Contracts\Model\Tokens\TokenStatusInterface;
use App\Models\Base\BaseTokenModel;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractTokenModel extends BaseTokenModel implements TokenStatusInterface
{

   // ────── 查詢範圍 Eloquent Scope Methods ──────
   
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // ────── 處理 Token 生命週期的 Status ──────

    public function proceedTo(string $status): bool
    {
        $this->status = $status;
        return parent::proceedTo($status);
    }


    public function isRequestDone(): bool
    {
        return $this->status !== 'pending';
    }

}
