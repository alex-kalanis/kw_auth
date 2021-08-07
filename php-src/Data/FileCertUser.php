<?php

namespace kalanis\kw_auth\Data;


use kalanis\kw_auth\Interfaces\IUserCert;


/**
 * Class FileCertUser
 * @package kalanis\kw_auth\Data
 */
class FileCertUser extends FileUser implements IUserCert
{
    protected $key = '';
    protected $salt = '';

    public function addCertInfo(string $key, string $salt): void
    {
        $this->key = $key;
        $this->salt = $salt;
    }

    public function getPubKey(): string
    {
        return $this->key;
    }

    public function getPubSalt(): string
    {
        return $this->salt;
    }
}
