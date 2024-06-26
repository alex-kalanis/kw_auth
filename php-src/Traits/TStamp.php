<?php

namespace kalanis\kw_auth\Traits;


/**
 * Trait TStamp
 * @package kalanis\kw_auth\Methods
 * Processing timestamp check
 */
trait TStamp
{
    protected int $timeDifference = 100;

    protected function initStamp(int $maxDiff): void
    {
        $this->timeDifference = $maxDiff;
    }

    protected function checkStamp(int $sentTime): bool
    {
        $current = time();
        return (($current + $this->timeDifference > $sentTime) && ($current - $this->timeDifference < $sentTime));
    }
}
