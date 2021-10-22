<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;


/**
 * Interface IExpire
 * @package kalanis\kw_auth\Interfaces
 * Authentication system expires at...
 */
interface IExpire
{
    /**
     * Set if authentication will expire in preset time
     * @param bool $expiry
     * @throws AuthException
     */
    public function setExpireNotice(bool $expiry): void;

    /**
     * Return if authentication expire in following time
     * @return bool
     * @throws AuthException
     */
    public function willExpire(): bool;

    /**
     * When it expire
     * @return int
     * @throws AuthException
     */
    public function getExpireTime(): int;

    /**
     * Set time of expiration
     * @param int $expireTime
     * @throws AuthException
     */
    public function updateExpireTime(int $expireTime): void;
}
