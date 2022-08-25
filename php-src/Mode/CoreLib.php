<?php

namespace kalanis\kw_auth\Mode;


use kalanis\kw_auth\Interfaces\IMode;


/**
 * Class CoreLib
 * @package kalanis\kw_auth\Mode
 * Authenticate via core libraries
 */
class CoreLib implements IMode
{
    public function check(string $pass, string $hash): bool
    {
        return password_verify($pass, $hash);
    }

    public function hash(string $pass, ?string $method = null): string
    {
        return strval(password_hash($pass, PASSWORD_DEFAULT));
    }
}
