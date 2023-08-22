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
}


class MockMethod extends Methods\AMethods
{
    public function process(\ArrayAccess $credentials): void
    {
        $this->loggedUser = new \MockUserToFill(
            '0',
            'Testing',
            '0',
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
