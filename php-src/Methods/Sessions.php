<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_auth\Interfaces\IAuth;


/**
 * Class Sessions
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Session
 * @codeCoverageIgnore because access external content
 */
class Sessions extends AMethods
{
    const INPUT_IP = 'acc_ip';
    const INPUT_NAME = 'acc_name';

    protected $session = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $session)
    {
        parent::__construct($authenticator, $nextOne);
        $this->session = $session;
    }

    public function process(ArrayAccess $credentials): void
    {
        if (PHP_SESSION_NONE == session_status()) {
            session_start();
        }
        if ($this->tryLogged()) {
            $this->loggedUser = $this->authenticator->getDataOnly($this->nameFromSess());
        } else {
            $name = $credentials->offsetExists('user') ? $credentials->offsetGet('user') : '' ;
            $pass = $credentials->offsetExists('pass') ? $credentials->offsetGet('pass') : '' ;
            $pass = $credentials->offsetExists('password') ? $credentials->offsetGet('password') : $pass ;
            if (!empty($name) && !empty($pass)) {
                $this->loggedUser = $this->authenticator->authenticate($name, ['password' => $pass]);
            }
        }
        $this->clearSession();
        if ($this->loggedUser) {
            $this->fillSession($this->loggedUser->getAuthName());
        }
    }

    public function remove(): void
    {
        if (PHP_SESSION_ACTIVE == session_status()) {
            session_destroy();
        }
    }

    protected function tryLogged(): bool
    {
        return (
            $this->session->offsetExists(static::INPUT_NAME)
            && !empty($this->session->offsetGet(static::INPUT_NAME)) // user has name already set
            && $this->session->offsetExists(static::INPUT_IP)
            && !empty($this->session->offsetGet(static::INPUT_IP)) // user has already set known ip
            && ($_SERVER["REMOTE_ADDR"] == $this->session->offsetGet(static::INPUT_IP)) // against proxy attack - changed ip through work
        );
    }

    protected function nameFromSess(): string
    {
        return strval($this->session->offsetGet(static::INPUT_NAME));
    }

    protected function fillSession(string $name): void
    {
        $this->session->offsetSet(static::INPUT_NAME, $name);
        $this->session->offsetSet(static::INPUT_IP, $_SERVER["REMOTE_ADDR"]);
    }

    protected function clearSession(): void
    {
        $this->session->offsetSet(static::INPUT_NAME, '');
        $this->session->offsetSet(static::INPUT_IP, '');
    }
}
