<?php

namespace kalanis\kw_auth\Interfaces;


use ArrayAccess;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods\AMethods;
use kalanis\kw_locks\LockException;


/**
 * Interface IAuthTree
 * @package kalanis\kw_auth\Interfaces
 * Authentication tree lookup
 */
interface IAuthTree
{
    /**
     * lookup for method which can authenticate send data
     * @param ArrayAccess $credentials
     * @return void
     * @throws AuthException
     * @throws LockException
     */
    public function findMethod(ArrayAccess $credentials): void;

    /**
     * Get the method which authenticated user; null for no match
     * @return AMethods|null
     * @throws AuthException
     */
    public function getMethod(): ?AMethods;
}
