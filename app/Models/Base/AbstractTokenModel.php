<?php

namespace App\Models\Base;

use App\Contracts\Model\Tokens\TokenStatusInterface;
use App\Models\Base\BaseTokenModel;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractTokenModel extends BaseTokenModel implements TokenStatusInterface
{

   // ────── 實作 Interface 的方法 ──────
   
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function proceedTo(string $status): static
    {
        $this->status = $status;
        return parent::proceedTo($status);
    }
}
