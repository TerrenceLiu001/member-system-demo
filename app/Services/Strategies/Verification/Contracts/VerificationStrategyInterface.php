<?php

namespace App\Services\Strategies\Verification\Contracts;

use App\Contracts\Model\Tokens\TokenStatusInterface;
use Illuminate\Http\Request;
use Exception;

interface VerificationStrategyInterface
{

    /**
     * 取得此驗證策略的唯一類型識別字串。
     *
     * @return string 策略的唯一類型字串
     */
    public function getType(): string;

    /**
     * 驗證 Request 是否有效，並返回資料
     * 
     * @param Request $input 請求資料
     * @return mixed 根據不同 Strategy 返回不同資料
     * @throws Exception 如果 Request 無效
     */
    public function validateAndPrepareRequest(Request $request): mixed;

    /**
     * 創建或更新資料庫的紀錄
     * 
     * @param mixed $preparedData validateAndPrepareRequest 返回的資料
     * @return TokenStatusInterface 創建或更新的「模型紀錄」
     * @throws Exception 如果操作失敗 
     */
    public function createAndUpdateRecord(mixed $preparedData): TokenStatusInterface;

    /**
     * 寄送驗證信件
     * 
     * @param TokenStatusInterface $record createOrUpdatePendingRecord 返回的「模型紀錄」
     * @param string $verificationLink 驗證連結
     * @return void
     * @throws Exception 如果發送信件失敗
     */
    public function dispatchVerificationEmail(TokenStatusInterface $record, string $verificationLink): void;


    /**
     * 提供生成驗證連結所需的路由名稱和參數。
     *
     * @param TokenStatusInterface $record createAndUpdateRecord 返回的「模型紀錄」
     * @return array 例如: ['routeName' => 'route_name', 'params' => ['email' => '...', 'token' => '...']]
     */
    public function getLinkInfo(TokenStatusInterface $record): array;
}
