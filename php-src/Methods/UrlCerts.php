<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_address_handler\Handler;
use kalanis\kw_auth\Traits\TStamp;
use kalanis\kw_auth_sources\Interfaces\IAuthCert;


/**
 * Class UrlCerts
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via certificates
 *
 * query:
 * //dummy/u:whoami/?pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq
 *
 * makes following call:
 * openssl_verify( $data = '//dummy/u:whoami/?pass=asdf123ghjk456&timestamp=123456&salt=789', $signature = 'poiuztrewq', $key = 'mnbvcx987' )
 *
 * - it removed digest value and added locally stored salt
 */
class UrlCerts extends AMethods
{
    use TStamp;

    const INPUT_NAME = 'name';
    const INPUT_NAME2 = 'user';
    const INPUT_STAMP = 'timestamp';
    const INPUT_DIGEST = 'digest';
    const INPUT_SALT = 'salt';

    /** @var IAuthCert */
    protected $authenticator;
    /** @var Handler */
    protected $uriHandler = null;

    public function __construct(IAuthCert $authenticator, ?AMethods $nextOne, Handler $uriHandler)
    {
        parent::__construct($authenticator, $nextOne);
        $this->uriHandler = $uriHandler;
    }

    public function process(\ArrayAccess $credentials): void
    {
        $name = $credentials->offsetExists(static::INPUT_NAME) ? strval($credentials->offsetGet(static::INPUT_NAME)) : '' ;
        $name = $credentials->offsetExists(static::INPUT_NAME2) ? strval($credentials->offsetGet(static::INPUT_NAME2)) : $name ;
        $stamp = $credentials->offsetExists(static::INPUT_STAMP) ? intval(strval($credentials->offsetGet(static::INPUT_STAMP))) : 0 ;

        $wantedUser = $this->authenticator->getCertData(strval($name));
        if ($wantedUser && !empty($stamp) && $this->checkStamp($stamp)) {
            // now we have public key and salt from our storage, so it's time to check it

            // digest out, salt in
            $digest = strval($this->uriHandler->getParams()->offsetGet(static::INPUT_DIGEST));
            $this->uriHandler->getParams()->offsetUnset(static::INPUT_DIGEST);
            $this->uriHandler->getParams()->offsetSet(static::INPUT_SALT, $wantedUser->getPubSalt());
            $data = strval($this->uriHandler->getAddress());

            // verify
            $result = openssl_verify($data, base64_decode(rawurldecode($digest)), $wantedUser->getPubKey(), OPENSSL_ALGO_SHA256);
            if (1 === $result) {
                // OK
                $this->loggedUser = $wantedUser;
            }
        }
    }

    public function remove(): void
    {
    }
}
