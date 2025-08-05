<?php

namespace App\Repositories\Tokens\Implementations;

use App\Models\UserContactUpdate;
use App\Repositories\Tokens\AbstractEloquentRepository;

class EloquentContactUpdateRepository extends AbstractEloquentRepository 
{

    public function getModel(): string
    {
        return UserContactUpdate::class;
    }

    public function findByEmailAndContactType(string $email, string $contactType): ?UserContactUpdate
    {
        $model = $this->getModel();
        return $model::email($email)->type($contactType)->latest('id')->first();
    }

    public function findPendingRecord(array $conditions): ?UserContactUpdate
    {
        [ 'user_id' => $userId, 'contact_type' => $type ] = $conditions;
        $model = $this->getModel();

        return $model::userId($userId)->type($type)->status('pending')->first();
    }

}