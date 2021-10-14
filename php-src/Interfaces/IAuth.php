<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;
use kalanis\kw_locks\LockException;


/**
 * Interface IAuth
 * @package kalanis\kw_auth\Interfaces
 * Authentication sources available on your system
 */
interface IAuth
{
    /**
     * Get data bout chosen user
     * @param string $userName
     * @return IUser|null
     * @throws AuthException
     * @throws LockException
     */
    public function getDataOnly(string $userName): ?IUser;

    /**
     * Check if credentials are okay
     * @param string $userName
     * @param string[] $params
     * @return IUser|null
     * @throws AuthException
     * @throws LockException
     */
    public function authenticate(string $userName, array $params = []): ?IUser;
}
