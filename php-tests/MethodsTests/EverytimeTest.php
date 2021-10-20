<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods;
use kalanis\kw_locks\LockException;


class EverytimeTest extends CommonTestClass
{
    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testMethod(): void
    {
        $method = new Methods\Everytime(new \MockAuth(), null);
        $this->assertFalse($method->isAuthorized());
        $method->process(new \MockCredentials());
        $this->assertTrue($method->isAuthorized());
        $this->assertNotEmpty($method->getLoggedUser());
        $this->assertEquals('Debug', $method->getLoggedUser()->getAuthName());
        $method->remove();
    }
}
