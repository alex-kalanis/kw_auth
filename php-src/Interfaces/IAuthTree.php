<?php

namespace kalanis\kw_auth\Interfaces;


use ArrayAccess;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods\AMethods;


/**
 * Interface IAuthTree
 * @package kalanis\kw_auth\Interfaces
 * Authentication tree lookup
 */
interface IAuthTree
{
    /**
     * lookup for method which can authenticate send data
     * @param ArrayAccess<string, string|int|float> $credentials
     * @throws AuthException
     */
    public function findMethod(ArrayAccess $credentials): void;

    /**
     * Get the method which authenticated user; null for no match
     * @throws AuthException
     * @return AMethods|null
     */
    public function getMethod(): ?AMethods;
}
