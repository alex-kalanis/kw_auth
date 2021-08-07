<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IUser;


/**.
 * Class AMethods
 * @package kalanis\kw_auth\AuthMethods
 * Chain of responsibility for authentication
 */
abstract class AMethods
{
    /** @var IAuth|null */
    protected $authenticator = null;
    /** @var AMethods|null */
    protected $nextOne = null;
    /** @var IUser|null */
    protected $loggedUser = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne)
    {
        $this->authenticator = $authenticator;
        $this->nextOne = $nextOne;
    }

    /**
     * @param \ArrayAccess $credentials
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
