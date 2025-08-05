<?php

namespace App\Models\Base;

use App\Contracts\Model\Tokens\TokenCapableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

abstract class BaseTokenModel extends Model implements TokenCapableInterface
{

    // ──────    實作 Interface 的方法        ──────

    abstract public function getTokenName(): string;    


    public function getTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->token_expires_at;
    }

    public function updateTokenAndExpiry(string $token, ?int $minutes = 10): static
    {
        $this->{$this->getTokenName()} = $token;
        $this->token_expires_at = now()->addMinutes($minutes);
        return $this;
    }

    // ────── 查詢範圍 Eloquent Scope Methods ──────

    public function scopeToken(Builder $query, $token): Builder
    {
        return $query->where($this->getTokenName(), $token);
    }

    public function scopeEmail(Builder $query, $email): Builder
    {
        return $query->where('email', $email);
    }

    public function scopeApplyNamedScopes(Builder $query, array $scopes): Builder
    {
        foreach ($scopes as $key => $value) {
            $method = Str::studly($key);
            if (method_exists(static::class, 'scope' . $method)) {
                $query->{$key}($value);
            }
        }
        return $query;
    }


    // ────── 操作與 token 欄位和效期有關的方法 ──────

    public function proceedTo(string $status): static
    {
        $this->clearTokenFields();
        return $this;
    }

    protected function clearTokenFields(): void
    {
        $this->{$this->getTokenName()} = null;
        $this->token_expires_at = null;
    }

}
