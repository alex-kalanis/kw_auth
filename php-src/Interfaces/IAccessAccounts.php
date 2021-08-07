<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;


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
     */
    public function createAccount(IUser $user, string $password): void;

    /**
     * @return IUser[]
     * @throws AuthException
     */
    public function readAccounts(): array;

    /**
     * @param IUser $user
     * @throws AuthException
     */
    public function updateAccount(IUser $user): void;

    /**
     * @param string $userName
     * @param string $passWord
     * @throws AuthException
     */
    public function updatePassword(string $userName, string $passWord): void;

    /**
     * @param string $userName
     * @throws AuthException
     */
    public function deleteAccount(string $userName): void;
}
