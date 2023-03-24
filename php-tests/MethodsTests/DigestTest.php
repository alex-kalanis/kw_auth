<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_address_handler\Sources;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Interfaces\IUserCert;
use kalanis\kw_auth\Methods;
use kalanis\kw_locks\LockException;


class DigestTest extends CommonTestClass
{
    protected $realm1 = 'KWCMS_Http_Digest';
    protected $realm2 = 'testrealm@host.com';

    /**
     * @param string $address
     * @param bool $isAuth
     * @param IUserCert|null $expectedUser    chce cert data, kde je pub key a v nem sdileny klic
     * @param string $expectedPass
     * @param array $incomingCredentials    vlastne _SERVER, kam se nacpou data z klienta, ktera se pak rozdeluji a kontroluji
     * @throws AuthException
     * @throws LockException
     * @dataProvider httpDigestProvider
     */
    public function testHttpDigest(string $address, bool $isAuth, ?IUserCert $expectedUser, string $expectedPass, array $incomingCredentials): void
    {
        $urlSource = new Sources\Sources();
        $urlSource->setAddress($address);
        $method = new Methods\HttpDigest(new \MockAuthCert($expectedUser, $expectedPass), null, new \MockCredentials($incomingCredentials));
        $method->process(new \MockCredentials());
        $this->assertEquals($isAuth, $method->isAuthorized());
    }

    public function httpDigestProvider(): array
    {
        $mockUser = new FileCertUser();
        $mockUser->setData(123, 'testing', 456, 789, 452, 'Testing', '/dunno/');
        $mockUser->addCertInfo('qwertziop', 'qwertziop');
        return [
            [
                '//dummy/u:whoami/?pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq',
                false,
                null,
                '',
                [],
            ],
            [
                '//dummy/?user=whoami&pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq',
                false,
                $mockUser,
                '',
                [],
            ],
            [
                '//dummy/?user=testing&pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq',
                true,
                $mockUser,
                'asdfghjkl',
                [Methods\HttpDigest::INPUT_METHOD => 'GET', Methods\HttpDigest::INPUT_DIGEST => 'username="Mufasa", realm="testrealm@host.com", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", uri="/dir/index.html", qop="auth", nc="00000001", cnonce="0a4f113b", response="9a4310460ec85329b0263d1f3e6a61af", opaque="5ccc069c403ebaf9f0171e9517f40e41"', ],
            ],
            [
                '//dummy/?user=testing&pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq',
                false,
                $mockUser,
                'asdfghjkl',
                [Methods\HttpDigest::INPUT_METHOD => 'GET', Methods\HttpDigest::INPUT_DIGEST => 'username="", realm="", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", uri="/dummy/", qop="auth", nc="00000002", cnonce="0b83e197", response="397e0e824048a9d9aab4792df4083ed6", opaque="5ccc069c403ebaf9f0171e9517f40e41"', ],
            ],
            [
                '//dummy/?user=testing&pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq',
                true,
                $mockUser,
                'asdfghjkl',
                [Methods\HttpDigest::INPUT_METHOD => 'GET', Methods\HttpDigest::INPUT_DIGEST => 'username="Tester", realm="KWCMS_Http_Digest", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", uri="/dummy/", qop="auth", nc="00000002", cnonce="0b83e197", response="397e0e824048a9d9aab4792df4083ed6", opaque="5ccc069c403ebaf9f0171e9517f40e41"', ],
            ],
        ];
    }
}
