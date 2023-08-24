<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces;
use kalanis\kw_accounts\Interfaces as acc_interfaces;


/**
 * Class Auth
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods - pass auth tree into module
 */
class Auth
{
    /** @var acc_interfaces\IUser|acc_interfaces\IUserCert|null */
    protected static $authenticator = null;
    /** @var acc_interfaces\IAuth|null */
    protected static $auth = null;
    /** @var acc_interfaces\IProcessGroups|null */
    protected static $groups = null;
    /** @var acc_interfaces\IProcessClasses|null */
    protected static $classes = null;
    /** @var acc_interfaces\IProcessAccounts|null */
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
     * @param acc_interfaces\IUser|acc_interfaces\IUserCert|null $authenticator
     */
    static public function setAuthenticator($authenticator): void
    {
        static::$authenticator = $authenticator;
    }

    /**
     * @return acc_interfaces\IUser|acc_interfaces\IUserCert|null
     */
    static public function getAuthenticator()
    {
        return static::$authenticator;
    }

    static public function setAuth(?acc_interfaces\IAuth $auth): void
    {
        static::$auth = $auth;
    }

    static public function getAuth(): ?acc_interfaces\IAuth
    {
        return static::$auth;
    }

    static public function setGroups(?acc_interfaces\IProcessGroups $groups): void
    {
        static::$groups = $groups;
    }

    static public function getGroups(): ?acc_interfaces\IProcessGroups
    {
        return static::$groups;
    }

    static public function setClasses(?acc_interfaces\IProcessClasses $classes): void
    {
        static::$classes = $classes;
    }

    static public function getClasses(): ?acc_interfaces\IProcessClasses
    {
        return static::$classes;
    }

    static public function setAccounts(?acc_interfaces\IProcessAccounts $accounts): void
    {
        static::$accounts = $accounts;
    }

    static public function getAccounts(): ?acc_interfaces\IProcessAccounts
    {
        return static::$accounts;
    }
}
