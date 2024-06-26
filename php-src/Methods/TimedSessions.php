<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_accounts\Interfaces\IAuth;
use SessionHandlerInterface;


/**
 * Class TimedSessions
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Session - timer for valid authentication
 * @codeCoverageIgnore external resource, Cannot start session when headers already sent
 */
class TimedSessions extends Sessions
{
    protected const INPUT_TIME = 'acc_time';

    protected int $loginTimeout = 0;

    /**
     * @param IAuth|null $authenticator
     * @param AMethods|null $nextOne
     * @param ArrayAccess<string, string|int> $session
     * @param ArrayAccess<string, string|int> $server
     * @param int $loginTimeout
     * @param SessionHandlerInterface|null $externalHandler
     */
    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $session, ArrayAccess $server, int $loginTimeout = 86400, ?SessionHandlerInterface $externalHandler = null)
    {
        parent::__construct($authenticator, $nextOne, $session, $server, $externalHandler);
        $this->loginTimeout = $loginTimeout;
    }

    protected function tryLogged(): bool
    {
        return (
            $this->session->offsetExists(static::SESSION_NAME)
            && !empty($this->session->offsetGet(static::SESSION_NAME))// user has name already set
            && $this->session->offsetExists(static::SESSION_IP)
            && !empty($this->session->offsetGet(static::SESSION_IP)) // user has already set known ip
            && $this->session->offsetExists(static::INPUT_TIME)
            && !empty($this->session->offsetGet(static::INPUT_TIME)) // user has already set last used time
            && ($this->server->offsetGet(static::SERVER_REMOTE) == $this->session->offsetGet(static::SESSION_IP)) // against proxy attack - changed ip through work
            && ((intval(strval($this->session->offsetGet(static::INPUT_TIME))) + $this->loginTimeout) > time()) // kick-off on time delay
        );
    }

    protected function fillSession(string $name): void
    {
        parent::fillSession($name);
        $this->session->offsetSet(static::INPUT_TIME, time()); // set new timestamp
    }

    protected function clearSession(): void
    {
        parent::clearSession();
        $this->session->offsetSet(static::INPUT_TIME, 0);
    }
}
