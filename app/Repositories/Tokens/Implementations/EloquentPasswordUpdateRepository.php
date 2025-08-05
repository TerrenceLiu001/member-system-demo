<?php

namespace App\Repositories\Tokens\Implementations;

use App\Models\PasswordUpdate;
use App\Repositories\Tokens\AbstractEloquentRepository;

class EloquentPasswordUpdateRepository extends AbstractEloquentRepository 
{

    public function getModel(): string
    {
        return PasswordUpdate::class;
    }

    public function findPendingRecord(array $conditions): ?PasswordUpdate
    {
        [ 'user_id' => $userId, 'email' => $email] = $conditions;
        $model = $this->getModel();

        return $model::userId($userId)->email($email)->type('forgot')->status('pending')->first();
    }

    public function handlePasswordToken(PasswordUpdate $record, string $token): void
    {
        parent::handleToken($record, $token, 5);
    }

}