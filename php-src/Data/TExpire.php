<?php

namespace kalanis\kw_auth\Data;


/**
 * Trait TExpire
 * @package kalanis\kw_auth\Data
 * Expiration
 */
trait TExpire
{
    protected $showNotice = false;
    protected $expireTime = 0;

    public function setExpireNotice(bool $expiry): void
    {
        $this->showNotice = $expiry;
    }

    public function willExpire(): bool
    {
        return $this->showNotice;
    }

    public function getExpireTime(): int
    {
        return $this->expireTime;
    }

    public function updateExpireTime(int $expireTime): void
    {
        $this->expireTime = $expireTime;
    }
}
