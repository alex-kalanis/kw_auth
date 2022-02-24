<?php

namespace kalanis\kw_auth\Mode;


use kalanis\kw_auth\Interfaces\IMode;


/**
 * Class Md5
 * @package kalanis\kw_auth\Mode
 * Authenticate via Md5 checksum (UNSAFE!!!)
 */
class Md5 implements IMode
{
    public function check(string $pass, string $hash): bool
    {
        return md5($pass) == $hash;
    }

    public function hash(string $pass, ?string $method = null): string
    {
        return md5($pass);
    }
}
