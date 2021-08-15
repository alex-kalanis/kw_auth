<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_auth\Interfaces\IAuth;


/**
 * Class HttpUser
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Http User
 * @link https://www.php.net/manual/en/features.http-auth.php
 */
class HttpUser extends AMethods
{
    const INPUT_NAME = 'PHP_AUTH_USER';
    const INPUT_PASS = 'PHP_AUTH_PW';

    protected $realm = 'KWCMS_Http_User';

    protected $server = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $server)
    {
        parent::__construct($authenticator, $nextOne);
        $this->server = $server;
    }

    public function process(ArrayAccess $credentials): void
    {
        $name = $this->server->offsetExists(static::INPUT_NAME) ? $this->server->offsetGet(static::INPUT_NAME) : '' ;
        $pass = $this->server->offsetExists(static::INPUT_PASS) ? $this->server->offsetGet(static::INPUT_PASS) : '' ;

        if (!empty($name) && !empty($pass)) {
            $this->loggedUser = $this->authenticator->authenticate($name, ['password' => $pass]);
        }
    }

    public function remove(): void
    {
        $this->authNotExists();
    }

    public function authNotExists(): void
    {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="' . $this->realm . '"');
    }
}
