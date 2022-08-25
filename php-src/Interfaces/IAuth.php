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
     * @throws AuthException
     * @throws LockException
     * @return IUser|null
     */
    public function getDataOnly(string $userName): ?IUser;

    /**
     * Check if credentials are okay
     * @param string $userName
     * @param string[] $params
     * @throws AuthException
     * @throws LockException
     * @return IUser|null
     */
    public function authenticate(string $userName, array $params = []): ?IUser;
}
