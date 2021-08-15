<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAuth;


/**
 * Class CountedSessions
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Session - count tries
 */
class CountedSessions extends AMethods
{
    const INPUT_NAME = 'name';
    const INPUT_COUNTER = 'log_count';

    protected $maxTries = 100;
    protected $session = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $session, int $maxTries = 100)
    {
        parent::__construct($authenticator, $nextOne);
        $this->session = $session;
        $this->maxTries = $maxTries;
    }

    public function process(ArrayAccess $credentials): void
    {
        if (PHP_SESSION_NONE == session_status()) {
            session_start();
        }
        if (!empty($credentials->offsetExists(static::INPUT_NAME))) {
            if (!$this->session->offsetExists(static::INPUT_COUNTER)) {
                $this->session->offsetSet(static::INPUT_COUNTER, 0);
            }
            if ($this->session->offsetGet(static::INPUT_COUNTER) < $this->maxTries) {
                $this->session->offsetSet(static::INPUT_COUNTER, $this->session->offsetGet(static::INPUT_COUNTER) + 1);
            } else {
                throw new AuthException('Too many tries!', 429);
            }
        }
    }

    public function remove(): void
    {
        if (PHP_SESSION_ACTIVE == session_status()) {
            session_destroy();
        }
    }
}
