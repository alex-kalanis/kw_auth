<?php

namespace kalanis\kw_auth;


use ArrayAccess;
use kalanis\kw_accounts\AccountsException;
use kalanis\kw_auth\Interfaces\IAuthTree;
use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Traits\TLang;


/**
 * Class AuthTree
 * @package kalanis\kw_auth
 * Authenticate the user with predefined methods
 */
class AuthTree implements IAuthTree
{
    use TLang;

    protected ?Methods\AMethods $authTree = null;
    protected ?Methods\AMethods $usedMethod = null;

    public function __construct(?IKauTranslations $lang = null)
    {
        $this->setAuLang($lang);
    }

    public function setTree(?Methods\AMethods $authTree): void
    {
        $this->usedMethod = null;
        $this->authTree = $authTree;
    }

    /**
     * @param ArrayAccess<string, string|int|float> $credentials
     * @throws AccountsException
     * @throws AuthException
     */
    public function findMethod(ArrayAccess $credentials): void
    {
        $currentMethod = $this->getAuthTree();
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

    /**
     * @throws AuthException
     * @return Methods\AMethods
     */
    protected function getAuthTree(): Methods\AMethods
    {
        if (!$this->authTree) {
            throw new AuthException($this->getAuLang()->kauNoAuthTreeSet());
        }
        return $this->authTree;
    }
}
