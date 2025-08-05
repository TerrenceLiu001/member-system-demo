<?php

namespace App\Models;

use App\Contracts\MemberTokenInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model implements MemberTokenInterface
{
    use HasFactory;

    protected $table = 'member_center_guests';

    protected $fillable = [
        'email',
        'register_token',
        'token_expires_at',
        'status',
    ];

    protected $hidden = [
        'register_token', 
    ];


    protected $casts = [
        'token_expires_at' => 'datetime',
        'created_at' => 'datetime', 
        'updated_at' => 'datetime', 
    ];



    public function scopeCancelPending($query, $email)
    {
        return $query->where('email', $email)
                     ->where('status', 'pending')
                     ->update(['status' => 'cancel']);
    }


    public function getTokenExpiresAt(): ?DateTimeInterface
    {
        return $this->token_expires_at;   
    }

    public function updateTokenAndExpiry(string $token, ?int $time = 10): void
    {
        $this->register_token = $token;
        $this->token_expires_at = now()->addMinutes($time);
        $this->save();
    }


}