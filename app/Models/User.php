<?php

namespace App\Models;

use App\Models\Base\BaseTokenModel;

class User extends BaseTokenModel 
{

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

    public function getTokenName(): string
    {
        return 'bearer_token'; 
    }


    public static function isRegistered(string $contact, string $type = 'email'): ?User
    {
        return ($type === 'email')?  static::where('email', $contact)->first() : static::where('mobile', $contact)->first();          
    }
    
}
