<?php

namespace App\Repositories\Tokens\Implementations;

use App\Models\Guest;
use App\Repositories\Tokens\AbstractEloquentRepository;

class EloquentGuestRepository extends AbstractEloquentRepository 
{

    public function getModel(): string
    {
        return Guest::class;
    }

    public function findPendingRecord(array $conditions): ?Guest
    {
        $email = $conditions['email'] ?? null;
        if (!$email) return null;

        return $this->getModel()::email($email)->status('pending')->first();   
    }

}