<?php
namespace App\Contracts\Model\Tokens;

interface TokenCapableInterface
{
    public function getTokenName(): string;
    public function getTokenExpiresAt(): ?\DateTimeInterface;
    public function updateTokenAndExpiry(string $token, ?int $minutes = 10): void;
    public function proceedTo(string $status): bool;
}
