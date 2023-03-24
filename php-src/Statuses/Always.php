<?php

namespace kalanis\kw_auth\Statuses;


use kalanis\kw_auth\Interfaces;


/**
 * Class Always
 * @package kalanis\kw_auth\Statuses
 * Authenticate - no status check for access
 */
class Always implements Interfaces\IStatus
{
    public function allowLogin(?int $status): bool
    {
        return true;
    }

    public function allowCert(?int $status): bool
    {
        return true;
    }
}
