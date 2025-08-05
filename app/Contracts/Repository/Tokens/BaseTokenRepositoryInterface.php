<?php

namespace App\Contracts\Repository\Tokens;

use App\Models\Base\BaseTokenModel;
use App\Contracts\Model\Tokens\TokenCapableInterface;

/**
 * 所有與「Token」相關的 Repository 都應該具備的基礎功能。
 */
interface BaseTokenRepositoryInterface
{
    public function create(array $data): BaseTokenModel;
    public function save(BaseTokenModel $record): void;
    public function delete(BaseTokenModel $record): void;
    public function handleToken(BaseTokenModel $record, string $token, int $time): void;
}