<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_auth\TTranslate;


/**
 * Class CountedSessions
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Session - count tries
 * @codeCoverageIgnore external resource, Cannot start session when headers already sent
 */
class CountedSessions extends AMethods
{
    use TTranslate;

    const INPUT_NAME = 'name';
    const INPUT_COUNTER = 'log_count';

    protected $maxTries = 100;
    protected $session = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $session, int $maxTries = 100, ?IKATranslations $lang = null)
    {
        parent::__construct($authenticator, $nextOne);
        $this->setLang($lang);
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
                throw new AuthException($this->getLang()->kauTooManyTries(), 429);
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
