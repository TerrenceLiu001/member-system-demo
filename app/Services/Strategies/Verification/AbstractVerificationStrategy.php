<?php

namespace App\Services\Strategies\Verification;

use App\Services\Strategies\Verification\Contracts\VerificationStrategyInterface;
use App\Services\ServiceRegistry;

abstract class AbstractVerificationStrategy implements VerificationStrategyInterface
{
    protected ServiceRegistry $services;

    public function __construct(ServiceRegistry $services) 
    {
        $this->services = $services;
    }
}