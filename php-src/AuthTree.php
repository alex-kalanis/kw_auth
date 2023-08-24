<?php

namespace kalanis\kw_auth;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_auth\Interfaces\IAuthTree;
use kalanis\kw_locks\LockException;


/**
 * Class AuthTree
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods
 */
class AuthTree implements IAuthTree
{
    /** @var Methods\AMethods */
    protected $authTree = null;
    /** @var Methods\AMethods|null */
    protected $usedMethod = null;

    public function setTree(Methods\AMethods $authTree): void
    {
        $this->usedMethod = null;
        $this->authTree = $authTree;
    }

    /**
     * @param \ArrayAccess<string, string|int|float> $credentials
     * @throws AccountsException
     * @throws AuthException
     * @throws LockException
     */
    public function findMethod(\ArrayAccess $credentials): void
    {
        $currentMethod = $this->authTree;
        do {
            $currentMethod->process($credentials);
            if ($currentMethod->isAuthorized()) {
                $this->usedMethod = $currentMethod;
                return;
            }
        } while ($currentMethod = $currentMethod->getNextMethod());
    }

    public function getMethod(): ?Methods\AMethods
    {
        return $this->usedMethod;
    }
}
