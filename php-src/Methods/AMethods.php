<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces\IAuth;
use kalanis\kw_auth_sources\Interfaces\IUser;
use kalanis\kw_locks\LockException;


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
     * @param \ArrayAccess<string, string|int|float> $credentials
     * @throws AuthException
     * @throws AuthSourcesException
     * @throws LockException
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
