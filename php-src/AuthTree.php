<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces\IAuthTree;


/**
 * Class AuthTree
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods
 */
class AuthTree implements IAuthTree
{
    /** @var Methods\AMethods|null */
    protected $authTree = null;
    /** @var Methods\AMethods|null */
    protected $usedMethod = null;

    public function setTree(Methods\AMethods $authTree): void
    {
        $this->usedMethod = null;
        $this->authTree = $authTree;
    }

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
