<?php

namespace App\Services;

use App\Services\ServiceRegistry;

class AbstractUnitService
{
    protected ServiceRegistry $services;

    public function __construct(ServiceRegistry $services)
    {
        $this->services = $services;
    }
}