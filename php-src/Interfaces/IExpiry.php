<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;


/**
 * Interface IExpiry
 * @package kalanis\kw_auth\Interfaces
 * Authentication expires at...
 */
interface IExpiry
{
    /**
     * Set if authentication will expire in preset time
     * @param bool $expiry
     * @throws AuthException
     */
    public function setExpiry(bool $expiry): void;

    /**
     * Return if authentication expire in following time
     * @return bool
     * @throws AuthException
     */
    public function willExpiry(): bool;
}
