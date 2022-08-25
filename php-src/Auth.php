<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces;


/**
 * Class Auth
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods - pass auth tree into module
 */
class Auth
{
    /** @var Interfaces\IUser|Interfaces\IUserCert|null */
    protected static $authenticator = null;
    /** @var Interfaces\IAuth|null */
    protected static $auth = null;
    /** @var Interfaces\IAccessGroups|null */
    protected static $groups = null;
    /** @var Interfaces\IAccessClasses|null */
    protected static $classes = null;
    /** @var Interfaces\IAccessAccounts|null */
    protected static $accounts = null;

    /** @var AuthTree|null */
    protected static $authTree = null;

    static public function fill(Methods\AMethods $authMethods): void
    {
        static::$authTree = new AuthTree();
        static::$authTree->setTree($authMethods);
    }

    static public function getTree(): ?Interfaces\IAuthTree
    {
        return static::$authTree;
    }

    /**
     * @param Interfaces\IUser|Interfaces\IUserCert|null $authenticator
     */
    static public function setAuthenticator($authenticator): void
    {
        static::$authenticator = $authenticator;
    }

    /**
     * @return Interfaces\IUser|Interfaces\IUserCert|null
     */
    static public function getAuthenticator()
    {
        return static::$authenticator;
    }

    static public function setAuth(?Interfaces\IAuth $auth): void
    {
        static::$auth = $auth;
    }

    static public function getAuth(): ?Interfaces\IAuth
    {
        return static::$auth;
    }

    static public function setGroups(?Interfaces\IAccessGroups $groups): void
    {
        static::$groups = $groups;
    }

    static public function getGroups(): ?Interfaces\IAccessGroups
    {
        return static::$groups;
    }

    static public function setClasses(?Interfaces\IAccessClasses $classes): void
    {
        static::$classes = $classes;
    }

    static public function getClasses(): ?Interfaces\IAccessClasses
    {
        return static::$classes;
    }

    static public function setAccounts(?Interfaces\IAccessAccounts $accounts): void
    {
        static::$accounts = $accounts;
    }

    static public function getAccounts(): ?Interfaces\IAccessAccounts
    {
        return static::$accounts;
    }
}
