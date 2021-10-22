<?php

namespace kalanis\kw_auth\Data;


/**
 * Trait TCerts
 * @package kalanis\kw_auth\Data
 * Work with certificates
 */
trait TCerts
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
