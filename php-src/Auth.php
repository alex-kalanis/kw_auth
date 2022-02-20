<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces\IAuthTree;


/**
 * Class Auth
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods - pass auth tree into module
 */
class Auth
{
    /** @var AuthTree|null */
    protected static $authenticator = null;

    /** @var AuthTree|null */
    protected static $authTree = null;

    static public function fill(Methods\AMethods $authMethods): void
    {
        static::$authTree = new AuthTree();
        static::$authTree->setTree($authMethods);
    }

    static public function getTree(): ?IAuthTree
    {
        return static::$authTree;
    }

    static public function setAuthenticator($authenticator): void
    {
        static::$authenticator = $authenticator;
    }

    static public function getAuthenticator()
    {
        return static::$authenticator;
    }
}
