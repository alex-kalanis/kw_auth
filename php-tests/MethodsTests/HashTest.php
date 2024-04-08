<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileCertUser;
use kalanis\kw_accounts\Interfaces\IUserCert;
use kalanis\kw_address_handler\Handler;
use kalanis\kw_address_handler\HandlerException;
use kalanis\kw_address_handler\Sources;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods;


class HashTest extends CommonTestClass
{
    /**
     * @param string $address
     * @param bool $isAuth
     * @param IUserCert|null $expectedUser
     * @param string $expectedPass
     * @param array $incomingCredentials
     * @throws AccountsException
     * @throws AuthException
     * @throws HandlerException
     * @dataProvider urlHashProvider
     */
    public function testUrlHash(string $address, bool $isAuth, ?IUserCert $expectedUser, string $expectedPass, array $incomingCredentials): void
    {
        $urlSource = new Sources\Sources();
        $urlSource->setAddress($address);
        $method = new Methods\UrlHash(new \MockAuthCert($expectedUser, $expectedPass), null, new Handler($urlSource), 'md5');
        $method->process(new \MockCredentials($incomingCredentials));
        $this->assertEquals($isAuth, $method->isAuthorized());
        $method->remove();
    }

    public function urlHashProvider(): array
    {
        $mockUser = new FileCertUser();
        $mockUser->setUserData('123', 'testing', '456', 789, 452, 'Testing', '/dunno/');
        $mockUser->updateCertInfo('qwertziop', 'qwertziop');
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
                false,
                $mockUser,
                'asdfghjkl',
                [Methods\UrlHash::INPUT_STAMP => time(), ],
            ],
            [
                '//dummy/?user=testing&pass=asdf123ghjk456&timestamp=123456&digest=poiuztrewq',
                false,
                $mockUser,
                'asdfghjkl',
                ['pass'=> 'lkjhgfdsa', ],
            ],
            [
                '//dummy/?user=testing&pass=asdf123ghjk456&timestamp=123456&digest=3d43f5b47be2258468c862fce574dfe0',
                true,
                $mockUser,
                'lkjhgfdsa',
                ['pass' => 'lkjhgfdsa', Methods\UrlHash::INPUT_NAME => 'someone', Methods\UrlHash::INPUT_STAMP => time(), ],
            ],
        ];
    }
}
