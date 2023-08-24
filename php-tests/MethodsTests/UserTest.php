<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileUser;
use kalanis\kw_accounts\Interfaces\IUser;
use kalanis\kw_address_handler\Sources;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods;


class UserTest extends CommonTestClass
{
    /**
     * @param string $address
     * @param bool $isAuth
     * @param IUser|null $expectedUser
     * @param string $expectedPass
     * @param array $incomingCredentials
     * @throws AccountsException
     * @throws AuthException
     * @dataProvider httpUserProvider
     */
    public function testHttpUser(string $address, bool $isAuth, ?IUser $expectedUser, string $expectedPass, array $incomingCredentials): void
    {
        $urlSource = new Sources\Sources();
        $urlSource->setAddress($address);
        $method = new Methods\HttpUser(new \MockAuth($expectedUser, $expectedPass), null, new \MockCredentials($incomingCredentials));
        $method->process(new \MockCredentials());
        $this->assertEquals($isAuth, $method->isAuthorized());
    }

    public function httpUserProvider(): array
    {
        $mockUser = new FileUser();
        $mockUser->setUserData('123', 'testing', '456', 789, 453, 'Testing', '/dunno/');
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
                [Methods\HttpUser::INPUT_NAME => 'testing', Methods\HttpUser::INPUT_PASS => 'asdfghjkl', ],
            ],
        ];
    }
}
