<?php

namespace App\Models;

use App\Contracts\MemberTokenInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Password;

class PasswordUpdate extends Model implements MemberTokenInterface
{
    use HasFactory;

    protected $table = 'member_center_password_update';

    protected $fillable = [
        'user_id',
        'email',
        'type',
        'password_token',
        'token_expires_at',
        'status',
    ];

    protected $hidden = [
        'password_token', 
    ];


    protected $casts = [
        'token_expires_at' => 'datetime',
        'created_at' => 'datetime', 
        'updated_at' => 'datetime', 
    ];

    


    // 將 status 從 「pending」 「給為 cancel」 (Type: forgot)
    public function scopeCancelPendingForgot($query, $userId, $email)
    {
        return $query->where('user_id', $userId)
                    ->where('email', $email)
                    ->where('type', 'forgot')
                    ->where('status', 'pending')
                    ->update(['status' => 'cancel']);
    }

    public function complete(): PasswordUpdate
    {
        if ($this->status === 'pending') 
        {
            $this->status = 'completed';
            $this->save();
        }
        return $this;
    }

    public function getTokenExpiresAt(): ?DateTimeInterface
    {
        return $this->token_expires_at;   
    }

    public function updateTokenAndExpiry(string $token, ?int $time = 10): void
    {
        $this->password_token = $token;
        $this->token_expires_at = now()->addMinutes($time);
        $this->save();
    }
}