<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_auth\Methods;


class EverytimeTest extends CommonTestClass
{
    /**
     * @throws \kalanis\kw_auth\AuthException
     * @throws \kalanis\kw_locks\LockException
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
