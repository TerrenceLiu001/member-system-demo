<?php

namespace App\Models;

use App\Contracts\MemberTokenInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContactUpdate extends Model implements MemberTokenInterface
{
    use HasFactory;

    protected $table = 'member_center_user_contact_update';

    protected $fillable = [
        'user_id',
        'email',
        'mobile',
        'contact_type',
        'new_contact',
        'update_contact_token',
        'token_expires_at',
        'verification_at',
        'status',
    ];

    protected $hidden = [
        'update_contact_token', 
    ];


    protected $casts = [
        'token_expires_at' => 'datetime',
        'verification_at' => 'datetime',
        'created_at' => 'datetime', 
        'updated_at' => 'datetime', 
    ];

    
    public function markVerification()
    {
        $this->status = 'verified';
        $this->verification_at = now();
        $this->save();
    }

    public function isRequestCompleted(): bool
    {

        return $this->status !== 'pending';
    }


    public function getTokenExpiresAt(): ?DateTimeInterface
    {
        return $this->token_expires_at;   
    }

    public function updateTokenAndExpiry(string $token, ?int $time = 10): void
    {
        $this->update_contact_token = $token;
        $this->token_expires_at = now()->addMinutes($time);
        $this->save();
    }
}