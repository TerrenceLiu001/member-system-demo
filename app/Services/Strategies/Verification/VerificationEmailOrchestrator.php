<?php

namespace App\Services\Strategies\Verification;

use App\Services\Strategies\Verification\Contracts\VerificationStrategyInterface;
use App\Services\Strategies\Verification\Implementations\RegisterVerificationStrategy;
use App\Services\Strategies\Verification\Implementations\ForgotPasswordVerificationStrategy;
use App\Services\Strategies\Verification\Implementations\UpdateContactVerificationStrategy;
use App\Services\MemberEmailService;
use Illuminate\Http\Request;


class VerificationEmailOrchestrator
{
    protected MemberEmailService $memberEmailService;
    protected RegisterVerificationStrategy $registerStrategy;
    protected ForgotPasswordVerificationStrategy $passwordStrategy;
    protected UpdateContactVerificationStrategy $contactStrategy;


    public function __construct(
        MemberEmailService $memberEmailService,
        RegisterVerificationStrategy $registerStrategy,
        ForgotPasswordVerificationStrategy $passwordStrategy,
        UpdateContactVerificationStrategy $contactStrategy
    )
    {
        $this->memberEmailService = $memberEmailService;
        $this->registerStrategy   = $registerStrategy;
        $this->passwordStrategy   = $passwordStrategy;
        $this->contactStrategy    = $contactStrategy;
    }

    public function dispatchVerification(string $type, Request $request): void
    {
        $strategy = match ($type) {
            'register' => $this->registerStrategy,
            'forgot_password' => $this->passwordStrategy,
            'update_contact' => $this->contactStrategy,
        };

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