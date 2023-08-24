<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_accounts\Interfaces\IAuth;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Traits\TLang;
use SessionHandlerInterface;


/**
 * Class CountedSessions
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Session - count tries
 * @codeCoverageIgnore external resource, Cannot start session when headers already sent
 */
class CountedSessions extends AMethods
{
    use TLang;

    const INPUT_NAME = 'name';
    const INPUT_COUNTER = 'log_count';

    /** @var int */
    protected $maxTries = 100;
    /** @var ArrayAccess<string, string|int> */
    protected $session = null;
    /** @var SessionHandlerInterface|null */
    protected $externalHandler = null;

    /**
     * @param IAuth|null $authenticator
     * @param AMethods|null $nextOne
     * @param ArrayAccess<string, string|int> $session
     * @param int $maxTries
     * @param IKauTranslations|null $lang
     * @param SessionHandlerInterface|null $externalHandler
     */
    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $session, int $maxTries = 100, ?IKauTranslations $lang = null, ?SessionHandlerInterface $externalHandler = null)
    {
        parent::__construct($authenticator, $nextOne);
        $this->setAuLang($lang);
        $this->session = $session;
        $this->maxTries = $maxTries;
        $this->externalHandler = $externalHandler;
    }

    /**
     * @param ArrayAccess<string, string|int|float> $credentials
     * @throws AuthException
     */
    public function process(ArrayAccess $credentials): void
    {
        if (PHP_SESSION_NONE == session_status()) {
            if ($this->externalHandler) {
                session_set_save_handler($this->externalHandler, true);
            }
            session_start();
        }
        if (!empty($credentials->offsetExists(static::INPUT_NAME))) {
            if (!$this->session->offsetExists(static::INPUT_COUNTER)) {
                $this->session->offsetSet(static::INPUT_COUNTER, 0);
            }
            if (intval(strval($this->session->offsetGet(static::INPUT_COUNTER))) < $this->maxTries) {
                $this->session->offsetSet(static::INPUT_COUNTER, strval(intval(strval($this->session->offsetGet(static::INPUT_COUNTER))) + 1));
            } else {
                throw new AuthException($this->getAuLang()->kauTooManyTries(), 429);
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
