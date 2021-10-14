<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_address_handler\Handler;
use kalanis\kw_auth\Interfaces\IAuthCert;


/**
 * Class UrlHash
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via hashed values
 *
 * query:
 * //dummy/u:whoami/?pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq
 *
 * makes following call:
 * hash($algorithm = <md5 | sha256 | ...> , $key = 'mnbvcx987' . $data = '//dummy/u:whoami/?pass=asdf123ghjk456&timestamp=123456&salt=789' , $signature = 'poiuztrewq'
 *
 * - it removed digest value and added locally stored salt
 */
class UrlHash extends AMethods
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
    /** @var string */
    protected $algorithm = '';

    /**
     * @param IAuthCert $authenticator
     * @param AMethods|null $nextOne
     * @param Handler $uriHandler
     * @param string $algorithm for hash function
     * @link https://php.net/manual/en/function.hash.php
     */
    public function __construct(IAuthCert $authenticator, ?AMethods $nextOne, Handler $uriHandler, string $algorithm)
    {
        parent::__construct($authenticator, $nextOne);
        $this->uriHandler = $uriHandler;
        $this->algorithm = $algorithm;
    }

    public function process(\ArrayAccess $credentials): void
    {
        $name = $credentials->offsetExists(static::INPUT_NAME) ? strval($credentials->offsetGet(static::INPUT_NAME)) : '' ;
        $name = $credentials->offsetExists(static::INPUT_NAME2) ? strval($credentials->offsetGet(static::INPUT_NAME2) ): $name ;
        $stamp = $credentials->offsetExists(static::INPUT_STAMP) ? intval(strval($credentials->offsetGet(static::INPUT_STAMP))) : 0 ;

        $wantedUser = $this->authenticator->getCertData((string)$name);
        if ($wantedUser && !empty($stamp) && $this->checkStamp($stamp)) {
            // now we have private salt from our storage, so it's time to check it

            // digest out, salt in
            $digest = $this->uriHandler->getParams()->offsetGet(static::INPUT_DIGEST);
            $this->uriHandler->getParams()->offsetUnset(static::INPUT_DIGEST);
            $this->uriHandler->getParams()->offsetSet(static::INPUT_SALT, $wantedUser->getPubSalt());
            $data = $this->uriHandler->getAddress();

            // verify
            if (hash($this->algorithm, $wantedUser->getPubKey() . (string)$data) == (string)$digest) {
                // OK
                $this->loggedUser = $wantedUser;
            }
        }
    }

    public function remove(): void
    {
    }
}
