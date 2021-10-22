<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IExpire;


/**
 * Trait TExpiration
 * @package kalanis\kw_auth\Sources
 * Expiration of password
 */
trait TExpiration
{
    protected $changeInterval = 31536000; // 60×60×24×365 - one year
    protected $changeNotice = 2592000; // 60×60×24×30 - one month

    protected function initExpiry(int $changeInterval, int $changeNoticeBefore)
    {
        $this->changeInterval = $changeInterval;
        $this->changeNotice = $changeNoticeBefore;
    }

    /**
     * @param $class
     * @param int $nextChange
     * @throws AuthException
     */
    public function setExpirationNotice($class, int $nextChange): void
    {
        if ($class && is_object($class) && $class instanceof IExpire) {
            $class->setExpireNotice( $this->shallExpire($nextChange));
        }
    }

    protected function shallExpire(int $nextChange): bool
    {
        return $this->getTime() + $this->changeNotice > $nextChange;
    }

    /**
     * @param $class
     * @throws AuthException
     */
    public function updateExpirationTime($class): void
    {
        if ($class && is_object($class) && $class instanceof IExpire) {
            $class->updateExpireTime($this->whenItExpire());
        }
    }

    protected function whenItExpire(): int
    {
        return $this->getTime() + $this->changeInterval;
    }

    /**
     * @return int
     * @codeCoverageIgnore too dynamic
     */
    protected function getTime(): int
    {
        return time();
    }
}
