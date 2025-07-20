<?php

namespace App\Models;


use App\Models\Base\AbstractTokenModel;

class Guest extends AbstractTokenModel
{

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


    public function getTokenName():string
    {
        return 'register_token';
    }

}