<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods;
use kalanis\kw_locks\LockException;


class BasicTest extends CommonTestClass
{
    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testUser(): void
    {
        $method = new MockMethod(new \MockAuth(), null);
        $this->assertFalse($method->isAuthorized());
        $this->assertEmpty($method->getLoggedUser());
        $this->assertEmpty($method->getNextMethod());
        $method->process(new \MockCredentials());
        $this->assertTrue($method->isAuthorized());
        $this->assertNotEmpty($method->getLoggedUser());
        $this->assertEquals('Testing', $method->getLoggedUser()->getAuthName());
        $this->assertEmpty($method->getNextMethod());
    }

    /**
     * @param int $when
     * @param int $length
     * @param bool $pass
     * @dataProvider stampsProvider
     */
    public function testStamps(int $when, int $length, bool $pass): void
    {
        $lib = new MockStamp();
        $lib->setDiff($length);
        $this->assertEquals($pass, $lib->checkTime($when));
    }

    public function stampsProvider(): array
    {
        return [
            [ time() + 10, 20, true],
            [ time() - 10, 20, true],
            [ time() + 50, 20, false],
            [ time() - 50, 20, false],
        ];
    }
}


class MockMethod extends Methods\AMethods
{
    public function process(\ArrayAccess $credentials): void
    {
        $this->loggedUser = new \MockUserToFill(
            0,
            'Testing',
            0,
            9999,
            null,
            'Testing',
            'not_available\\:///'
        );
    }

    public function remove(): void
    {
    }
}


class MockStamp
{
    use Methods\TStamp;

    public function setDiff(int $timeDiff): void
    {
        $this->initStamp($timeDiff);
    }

    public function checkTime(int $time): bool
    {
        return $this->checkStamp($time);
    }
}
