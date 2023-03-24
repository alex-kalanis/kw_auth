<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;
use kalanis\kw_locks\LockException;


/**
 * Interface IAccessAccounts
 * @package kalanis\kw_auth\Interfaces
 * Accessing account manipulation
 */
interface IAccessAccounts
{
    /**
     * @param IUser $user
     * @param string $password
     * @throws AuthException
     * @throws LockException
     */
    public function createAccount(IUser $user, string $password): void;

    /**
     * @throws AuthException
     * @throws LockException
     * @return IUser[]
     */
    public function readAccounts(): array;

    /**
     * @param IUser $user
     * @throws AuthException
     * @throws LockException
     * @return bool
     */
    public function updateAccount(IUser $user): bool;

    /**
     * @param string $userName
     * @param string $passWord
     * @throws AuthException
     * @throws LockException
     * @return bool
     */
    public function updatePassword(string $userName, string $passWord): bool;

    /**
     * @param string $userName
     * @throws AuthException
     * @throws LockException
     * @return bool
     */
    public function deleteAccount(string $userName): bool;
}
