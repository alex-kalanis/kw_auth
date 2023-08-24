<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_accounts\Interfaces\IAuth;
use SessionHandlerInterface;


/**
 * Class Sessions
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via Session
 * @codeCoverageIgnore external resource, Cannot start session when headers already sent
 */
class Sessions extends AMethods
{
    const SESSION_IP = 'acc_ip';
    const SESSION_NAME = 'acc_name';
    const SERVER_REMOTE = 'REMOTE_ADDR';
    const INPUT_NAME = 'name';
    const INPUT_NAME2 = 'user';
    const INPUT_PASS = 'pass';
    const INPUT_PASS2 = 'password';

    /** @var ArrayAccess<string, string|int> */
    protected $session = null;
    /** @var ArrayAccess<string, string|int> */
    protected $server = null;
    /** @var SessionHandlerInterface|null */
    protected $externalHandler = null;

    /**
     * @param IAuth|null $authenticator
     * @param AMethods|null $nextOne
     * @param ArrayAccess<string, string|int> $session
     * @param ArrayAccess<string, string|int> $server
     * @param SessionHandlerInterface|null $externalHandler
     */
    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $session, ArrayAccess $server, ?SessionHandlerInterface $externalHandler = null)
    {
        parent::__construct($authenticator, $nextOne);
        $this->session = $session;
        $this->server = $server;
        $this->externalHandler = $externalHandler;
    }

    public function process(ArrayAccess $credentials): void
    {
        if (PHP_SESSION_NONE == session_status()) {
            if ($this->externalHandler) {
                session_set_save_handler($this->externalHandler, true);
            }
            session_start();
        }
        if ($this->tryLogged()) {
            /** @scrutinizer ignore-call */
            $this->loggedUser = $this->authenticator->getDataOnly($this->nameFromSess());
        } else {
            $name = $credentials->offsetExists(static::INPUT_NAME) ? strval($credentials->offsetGet(static::INPUT_NAME)) : '' ;
            $name = $credentials->offsetExists(static::INPUT_NAME2) ? strval($credentials->offsetGet(static::INPUT_NAME2)) : $name ;
            $pass = $credentials->offsetExists(static::INPUT_PASS) ? strval($credentials->offsetGet(static::INPUT_PASS)) : '' ;
            $pass = $credentials->offsetExists(static::INPUT_PASS2) ? strval($credentials->offsetGet(static::INPUT_PASS2)) : $pass ;
            if (!empty($name) && !empty($pass)) {
                /** @scrutinizer ignore-call */
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
            $this->session->offsetExists(static::SESSION_NAME)
            && !empty($this->session->offsetGet(static::SESSION_NAME)) // user has name already set
            && $this->session->offsetExists(static::SESSION_IP)
            && !empty($this->session->offsetGet(static::SESSION_IP)) // user has already set known ip
            && ($this->server->offsetGet(static::SERVER_REMOTE) == $this->session->offsetGet(static::SESSION_IP)) // against proxy attack - changed ip through work
        );
    }

    protected function nameFromSess(): string
    {
        return strval($this->session->offsetGet(static::SESSION_NAME));
    }

    protected function fillSession(string $name): void
    {
        $this->session->offsetSet(static::SESSION_NAME, $name);
        $this->session->offsetSet(static::SESSION_IP, strval($this->server->offsetGet(static::SERVER_REMOTE)));
    }

    protected function clearSession(): void
    {
        $this->session->offsetSet(static::SESSION_NAME, '');
        $this->session->offsetSet(static::SESSION_IP, '');
    }
}
