<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_address_handler\Handler;
use kalanis\kw_address_handler\Sources;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Methods;
use kalanis\kw_locks\LockException;


class CertsTest extends CommonTestClass
{
    /**
     * from PHP.net, updated for my use
     */
    public function testDummyCert(): void
    {
        //data you want to sign
        $data = 'my data qwertzuiopasdfghjklyxcvbnm1234567890';

        //create new private and public key
        $privateKey = openssl_pkey_new([
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $privateData = openssl_pkey_get_details($privateKey);
        $publicKey = openssl_pkey_get_public($privateData['key']);
        $publicData = openssl_pkey_get_details($publicKey); // now we must pass string as PublicKey, not resource

        // create signature
        openssl_sign($data, $signature, $privateKey, "sha256WithRSAEncryption");

        // pass as string
        $sig = rawurlencode(base64_encode($signature));

        // verify signature
        $ok = openssl_verify($data, base64_decode(rawurldecode($sig)), $publicData['key'], OPENSSL_ALGO_SHA256);
        $this->assertEquals(1, $ok);
    }

    /**
     * @throws AuthException
     * @throws LockException
     * Cannot use provider for this stuff
     */
    public function testUrlCert(): void
    {
        //create signature
        $privateKey = openssl_pkey_new([
            "private_key_bits" => 1024,  # not need too long for testing purposes
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $privateData = openssl_pkey_get_details($privateKey);
        $publicKey = openssl_pkey_get_public($privateData['key']);
        $publicData = openssl_pkey_get_details($publicKey);

        $mockUser = new FileCertUser();
        $mockUser->setData(123, 'testing', 456, 789, 3, 'Testing', '/dunno/');
        $mockUser->addCertInfo($publicData['key'], 'qwertziop');

        // now query itself
        $urlSource = new Sources\Sources();
        $urlSource->setAddress($this->signLink($privateKey, '/dummy/?user=testing&pass=asdf123ghjk456&timestamp=123456', 'qwertziop'));
        $method = new Methods\UrlCerts(new \MockAuthCert($mockUser, ''), null, new Handler($urlSource) );
        $method->process(new \MockCredentials([Methods\UrlHash::INPUT_NAME => 'testing', Methods\UrlHash::INPUT_STAMP => time(), ]));
        $this->assertTrue($method->isAuthorized());
        $method->remove();
    }

    protected function signLink($privateKey, string $link, string $salt): string
    {
        $data = $link . '&salt=' . $salt;
        openssl_sign($data, $signature, $privateKey, "sha256WithRSAEncryption");
        return $link . '&digest=' . rawurlencode(base64_encode($signature));
    }

    /**
     * @throws AuthException
     * @throws LockException
     * Cannot use provider for this stuff
     */
    public function testHttpCert(): void
    {
        //create signature
        $privateKey = openssl_pkey_new([
            "private_key_bits" => 1024,  # not need too long for testing purposes
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $privateData = openssl_pkey_get_details($privateKey);
        $publicKey = openssl_pkey_get_public($privateData['key']);
        $publicData = openssl_pkey_get_details($publicKey);

        $mockUser = new FileCertUser();
        $mockUser->setData(123, 'testing', 456, 789, 3, 'Testing', '/dunno/');
        $mockUser->addCertInfo($publicData['key'], 'qwertziop');


        // address
        $urlSource = new Sources\Sources();
        $urlSource->setAddress('/dummy/?timestamp=123456');

        // signed
        $data = $urlSource->getAddress() . '&salt=qwertziop';
        openssl_sign($data, $signature, $privateKey, "sha256WithRSAEncryption");

        // now query itself
        $method = new Methods\HttpCerts(new \MockAuthCert($mockUser, ''), null, new Handler($urlSource), new \MockCredentials(
            [Methods\HttpCerts::INPUT_NAME => 'testing', Methods\HttpCerts::INPUT_PASS => rawurlencode(base64_encode($signature)), ]
        ) );
        $method->process(new \MockCredentials());
        $this->assertTrue($method->isAuthorized());
    }
}
