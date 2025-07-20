<?php

namespace App\Models;

use App\Models\Base\AbstractTokenModel;
use Illuminate\Database\Eloquent\Builder; 

class PasswordUpdate extends AbstractTokenModel 
{

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

    
    public function getTokenName():string
    {
        return 'password_token';
    }

    // ────── 查詢範圍 Scope Methods ──────

    public function scopeUserId(Builder $query, int $id): Builder
    {
        return $query->where('user_id', $id);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

}