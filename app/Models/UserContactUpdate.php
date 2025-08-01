<?php

namespace App\Models;

use App\Models\Base\AbstractTokenModel;
use Illuminate\Contracts\Database\Eloquent\Builder;

class UserContactUpdate extends AbstractTokenModel
{

    protected $table = 'member_center_user_contact_update';

    protected $fillable = [
        'user_id',
        'email',
        'mobile',
        'contact_type',
        'new_contact',
        'update_contact_token',
        'token_expires_at',
        'status',
    ];

    protected $hidden = [
        'update_contact_token', 
    ];


    protected $casts = [
        'token_expires_at' => 'datetime',
        'created_at' => 'datetime', 
        'updated_at' => 'datetime', 
    ];


    public function getTokenName():string
    {
        return 'update_contact_token';
    }

    // ────── 查詢範圍 Scope Methods ──────

    public function scopeUserId($query, $id): Builder
    {
        return $query->where('user_id', $id);
    }

    
    public function scopeType($query, $type): Builder
    {        
        return $query->where('contact_type', $type);
    }

}