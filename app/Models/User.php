<?php

namespace App\Models;

use App\Contracts\MemberTokenInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class User extends Model implements MemberTokenInterface
{
    use HasFactory, Notifiable;

    protected $table = 'member_center_users';

    protected $fillable = [
        'guest_id',
        'username',
        'email',
        'mobile',
        'country',
        'gender',
        'age_group',
        'address',
        'password',
        'bearer_token',
        'token_expires_at',
    ];

    protected $hidden = [
        'password',
        'bearer_token',
    ];

    protected $casts = [

        'token_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function isRegistered(string $contact, string $type = 'email'): ?User
    {
        return ($type === 'email')?  static::where('email', $contact)->first() : static::where('mobile', $contact)->first();          
    }
    
    // --- 實作 MemberTokenInterface ---

    public function getTokenExpiresAt(): ?DateTimeInterface
    {
        return $this->token_expires_at;
    }

    public function updateTokenAndExpiry(string $token, ?int $time = 12): void
    {
        $this->bearer_token = $token;
        $this->token_expires_at = now()->addHours($time);
        $this->save();
        
    }

}
