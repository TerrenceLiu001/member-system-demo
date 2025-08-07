<?php

namespace App\Services\Strategies\Verification;

use App\Services\Strategies\Verification\Contracts\VerificationStrategyInterface;
use App\Services\MemberEmailService;
use Illuminate\Http\Request;


class VerificationEmailOrchestrator
{
    protected MemberEmailService $memberEmailService;
    protected array $strategies = [];

    public function __construct(
        MemberEmailService $memberEmailService,
        iterable $strategies  
    ) {
        $this->memberEmailService = $memberEmailService;
        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->getType()] = $strategy;
        }
    }

    public function dispatchVerification(string $type, Request $request): void
    {
        $strategy = $this->strategies[$type];
        $this->verificationFlow($strategy, $request);
    }


    private function verificationFlow(VerificationStrategyInterface $strategy, Request $request): void
    {
        $data = $strategy->validateAndPrepareRequest($request);
        $record = $strategy->createAndUpdateRecord($data);
        $linkInfo = $strategy->getLinkInfo($record);

        $verificationLink = $this->memberEmailService->generateLink(
            $linkInfo['routeName'],
            $linkInfo['params']
        );

        $strategy->dispatchVerificationEmail($record, $verificationLink);
    }
}