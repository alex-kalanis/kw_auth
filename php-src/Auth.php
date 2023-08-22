<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces;
use kalanis\kw_auth_sources\Interfaces as sources_interfaces;


/**
 * Class Auth
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods - pass auth tree into module
 */
class Auth
{
    /** @var sources_interfaces\IUser|sources_interfaces\IUserCert|null */
    protected static $authenticator = null;
    /** @var sources_interfaces\IAuth|null */
    protected static $auth = null;
    /** @var sources_interfaces\IWorkGroups|null */
    protected static $groups = null;
    /** @var sources_interfaces\IWorkClasses|null */
    protected static $classes = null;
    /** @var sources_interfaces\IWorkAccounts|null */
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
     * @param sources_interfaces\IUser|sources_interfaces\IUserCert|null $authenticator
     */
    static public function setAuthenticator($authenticator): void
    {
        static::$authenticator = $authenticator;
    }

    /**
     * @return sources_interfaces\IUser|sources_interfaces\IUserCert|null
     */
    static public function getAuthenticator()
    {
        return static::$authenticator;
    }

    static public function setAuth(?sources_interfaces\IAuth $auth): void
    {
        static::$auth = $auth;
    }

    static public function getAuth(): ?sources_interfaces\IAuth
    {
        return static::$auth;
    }

    static public function setGroups(?sources_interfaces\IWorkGroups $groups): void
    {
        static::$groups = $groups;
    }

    static public function getGroups(): ?sources_interfaces\IWorkGroups
    {
        return static::$groups;
    }

    static public function setClasses(?sources_interfaces\IWorkClasses $classes): void
    {
        static::$classes = $classes;
    }

    static public function getClasses(): ?sources_interfaces\IWorkClasses
    {
        return static::$classes;
    }

    static public function setAccounts(?sources_interfaces\IWorkAccounts $accounts): void
    {
        static::$accounts = $accounts;
    }

    static public function getAccounts(): ?sources_interfaces\IWorkAccounts
    {
        return static::$accounts;
    }
}
