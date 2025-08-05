<?php

namespace App\Repositories\Tokens\Implementations;

use App\Models\User;
use App\Repositories\Tokens\BaseEloquentRepository;

class EloquentUserRepository extends BaseEloquentRepository
{

    public function getModel(): string
    {
        return User::class;
    }

    public function findAccount(string $contact, string $type = 'email'): ?User
    {
        $model = $this->getModel();

        return ($type === 'email')
            ? $model::email($contact)->first() 
            : $model::mobile($contact)->first();          
    }

    public function resetPassword(int $userId, string $newPassword): ?User
    {
        $model = $this->getModel();
        $user = $model::find($userId);

        if ($user) {
            $user->password = $newPassword;
            $user->save();
        }
        return $user;
    }

    public function cleanBearerToken(User $user): void
    {
        $user->update([
            'bearer_token' => null,
            'token_expires_at' => null
        ]);
    }
}