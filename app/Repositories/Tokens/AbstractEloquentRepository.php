<?php

namespace App\Repositories\Tokens;

use App\Contracts\Repository\Tokens\StatusTokenRepositoryInterface;
use App\Repositories\Tokens\BaseEloquentRepository;
use App\Models\Base\AbstractTokenModel;

abstract class AbstractEloquentRepository extends BaseEloquentRepository implements StatusTokenRepositoryInterface
{

    abstract public function findPendingRecord(array $conditions): ?AbstractTokenModel;

    public function create(array $data): AbstractTokenModel
    {
        return parent::create($data);
    }

    public function markStatus(?AbstractTokenModel $record, string $status): void
    {
        $record->proceedTo($status)?->save();
    }

    public function cancelPending(array $conditions): void
    {
        $this->findPendingRecord($conditions)?->proceedTo('cancel');
    }
}