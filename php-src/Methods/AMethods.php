<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Interfaces\IAuth;
use kalanis\kw_accounts\Interfaces\IUser;
use kalanis\kw_auth\AuthException;


/**.
 * Class AMethods
 * @package kalanis\kw_auth\AuthMethods
 * Chain of responsibility for authentication
 */
abstract class AMethods
{
    protected ?IAuth $authenticator = null;
    protected ?AMethods $nextOne = null;
    protected ?IUser $loggedUser = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne)
    {
        $this->authenticator = $authenticator;
        $this->nextOne = $nextOne;
    }

    /**
     * @param \ArrayAccess<string, string|int|float> $credentials
     * @throws AccountsException
     * @throws AuthException
     */
    abstract public function process(\ArrayAccess $credentials): void;

    abstract public function remove(): void;

    public function isAuthorized(): bool
    {
        return !empty($this->loggedUser);
    }

    public function getLoggedUser(): ?IUser
    {
        return $this->loggedUser;
    }

    public function getNextMethod(): ?AMethods
    {
        return $this->nextOne;
    }
}
