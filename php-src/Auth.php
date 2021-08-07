<?php

namespace kalanis\kw_auth;


/**
 * Class Auth
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods
 */
class Auth
{
    /** @var Methods\AMethods|null */
    protected static $authTree = null;
    /** @var Methods\AMethods|null */
    protected static $usedMethod = null;

    static public function init(Methods\AMethods $authTree): void
    {
        static::$authTree = $authTree;
    }

    /**
     * @param \ArrayAccess $credentials
     * @throws AuthException
     */
    static public function findMethod(\ArrayAccess $credentials): void
    {
        $currentMethod = static::$authTree;
        do {
            $currentMethod->process($credentials);
            if ($currentMethod->isAuthorized()) {
                static::$usedMethod = $currentMethod;
                return;
            }
        } while ($currentMethod = $currentMethod->getNextMethod());
    }

    static public function getMethod(): ?Methods\AMethods
    {
        return static::$usedMethod;
    }
}
