<?php
namespace App\Contracts;

interface MemberTokenInterface
{
    public function getTokenExpiresAt(): ?\DateTimeInterface;
}
