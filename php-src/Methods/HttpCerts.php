<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_address_handler\Handler;
use kalanis\kw_auth\Interfaces\IAuthCert;


/**
 * Class HttpCerts
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via http certificates
 * @codeCoverageIgnore because access openssl library
 * - public on server, private on client whom manage the site
 */
class HttpCerts extends AMethods
{
    const INPUT_NAME = 'PHP_AUTH_USER';
    const INPUT_PASS = 'PHP_AUTH_DIGEST';
    const INPUT_SALT = 'salt';

    /** @var IAuthCert */
    protected $authenticator;
    /** @var Handler */
    protected $uriHandler = null;
    /** @var ArrayAccess */
    protected $server = null;

    public function __construct(IAuthCert $authenticator, ?AMethods $nextOne, Handler $uriHandler, ArrayAccess $server)
    {
        parent::__construct($authenticator, $nextOne);
        $this->uriHandler = $uriHandler;
        $this->server = $server;
    }

    public function process(\ArrayAccess $credentials): void
    {
        $name = $this->server->offsetExists(static::INPUT_NAME) ? $this->server->offsetGet(static::INPUT_NAME) : '' ;
        $digest = $this->server->offsetExists(static::INPUT_PASS) ? $this->server->offsetGet(static::INPUT_PASS) : '' ;
        $wantedUser = $this->authenticator->getCertData((string)$name);
        if ($wantedUser && $digest) {
            // now we have public key and salt from our storage, so it's time to check it

            // salt in
            $this->uriHandler->getParams()->offsetSet(static::INPUT_SALT, $wantedUser->getPubSalt());

            // verify
            $result = @openssl_verify((string)$this->uriHandler->getAddress(), (string)$digest, $wantedUser->getPubKey());
            if (1 === $result) {
                // OK
                $this->loggedUser = $wantedUser;
            }
        }
    }

    public function remove(): void
    {
        $this->authNotExists();
    }

    public function authNotExists(): void
    {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: DigestCert');
    }
}
