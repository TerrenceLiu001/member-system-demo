<?php

namespace App\Services;

use App\Services\MemberAuthService;
use App\Services\MemberEmailService;
use App\Services\MemberEditService;
use App\Services\ValidationService;
use App\Repositories\Tokens\Implementations\EloquentUserRepository;

class ServiceRegistry 
{
    public MemberAuthService $memberAuthService;
    public MemberEmailService $memberEmailService;
    public MemberEditService $memberEditService;
    public ValidationService $validationService;
    public EloquentUserRepository $userRepository;

    public function __construct(
        MemberAuthService $memberAuthService,
        MemberEmailService $memberEmailService,
        MemberEditService $memberEditService,
        ValidationService $validationService,
        EloquentUserRepository $userRepository
    ) {
        $this->memberAuthService  = $memberAuthService;
        $this->memberEmailService = $memberEmailService;
        $this->memberEditService  = $memberEditService;
        $this->validationService  = $validationService;
        $this->userRepository     = $userRepository;
    }
}