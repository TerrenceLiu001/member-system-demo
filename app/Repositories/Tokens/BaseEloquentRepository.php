<?php

namespace App\Repositories\Tokens;

use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseTokenModel;
use App\Contracts\Model\Tokens\TokenCapableInterface;
use App\Contracts\Repository\Tokens\BaseTokenRepositoryInterface;

abstract class BaseEloquentRepository implements BaseTokenRepositoryInterface
{
    /**
     * @return Model
     */
    abstract public function getModel(): string;  


    public function create(array $data): BaseTokenModel
    {
        $model = $this->getModel();
        return $model::create($data);
    }

    public function update(BaseTokenModel $record, array $data): void
    {
        $record->update($data);
    }

    public function save(BaseTokenModel $record): void
    {
        $record->save();
    }

    public function delete(BaseTokenModel $record): void
    {
        $record->delete();
    }

    public function handleToken(BaseTokenModel $record, string $token, int $time): void
    {
        $record->updateTokenAndExpiry($token, $time);
        $this->save($record);
    }

}
