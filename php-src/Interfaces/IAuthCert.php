<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;
use kalanis\kw_locks\LockException;


/**
 * Interface IAuthCert
 * @package kalanis\kw_auth\Interfaces
 * Authentication sources available on your system - for certificates
 */
interface IAuthCert extends IAuth, IAccessAccounts
{
    /**
     * Update cert data in storage
     * @param string $userName
     * @param string|null $certKey
     * @param string|null $certSalt
     * @throws AuthException
     * @throws LockException
     */
    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): void;

    /**
     * Cet cert data from storage
     * @param string $userName
     * @throws AuthException
     * @throws LockException
     * @return IUserCert|null
     */
    public function getCertData(string $userName): ?IUserCert;
}
