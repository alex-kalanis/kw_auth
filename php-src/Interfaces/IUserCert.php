<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IUserCert
 * @package kalanis\kw_auth\Interfaces
 * User data from your auth system - with certificate
 */
interface IUserCert extends IUser
{
    /**
     * Fill certificates; null values will not change
     * @param string $key
     * @param string $salt
     */
    public function addCertInfo(?string $key, ?string $salt): void;

    public function getPubSalt(): string;

    public function getPubKey(): string;
}
