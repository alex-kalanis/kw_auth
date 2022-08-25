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
     */
    public function updateAccount(IUser $user): void;

    /**
     * @param string $userName
     * @param string $passWord
     * @throws AuthException
     * @throws LockException
     */
    public function updatePassword(string $userName, string $passWord): void;

    /**
     * @param string $userName
     * @throws AuthException
     * @throws LockException
     */
    public function deleteAccount(string $userName): void;
}
