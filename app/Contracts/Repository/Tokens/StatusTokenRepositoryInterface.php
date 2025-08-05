<?php

namespace App\Contracts\Repository\Tokens;
use App\Models\Base\AbstractTokenModel;

interface StatusTokenRepositoryInterface extends BaseTokenRepositoryInterface
{
    public function markStatus(?AbstractTokenModel $record, string $status): void;
    public function cancelPending(array $data): void;
}