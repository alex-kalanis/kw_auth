<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_auth\Interfaces\IAuth;


/**
 * Class TimedSessions
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Session - timer for valid authentication
 * @codeCoverageIgnore because access external content
 */
class TimedSessions extends Sessions
{
    const INPUT_TIME = 'acc_time';

    protected $loginTimeout = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $session, int $loginTimeout = 86400)
    {
        parent::__construct($authenticator, $nextOne, $session);
        $this->loginTimeout = $loginTimeout;
    }

    protected function tryLogged(): bool
    {
        return (
            $this->session->offsetExists(static::INPUT_NAME)
            && !empty($this->session->offsetGet(static::INPUT_NAME))// user has name already set
            && $this->session->offsetExists(static::INPUT_IP)
            && !empty($this->session->offsetGet(static::INPUT_IP)) // user has already set known ip
            && $this->session->offsetExists(static::INPUT_TIME)
            && !empty($this->session->offsetGet(static::INPUT_TIME)) // user has already set last used time
            && ($_SERVER["REMOTE_ADDR"] == $this->session->offsetGet(static::INPUT_IP)) // against proxy attack - changed ip through work
            && (($this->session->offsetGet(static::INPUT_TIME) + $this->loginTimeout) > time()) // kick-off on time delay
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
