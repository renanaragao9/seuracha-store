<?php

namespace App\Policies;

class CompanyPolicy extends BasePolicy
{
    protected function resourceCode(): string
    {
        return 'company';
    }
}
