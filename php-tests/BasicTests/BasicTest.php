<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_auth\Data;


class BasicTest extends CommonTestClass
{
    public function testUser(): void
    {
        $user = new Data\FileUser();
        $this->assertEmpty($user->getAuthId());
        $this->assertEmpty($user->getAuthName());
        $this->assertEmpty($user->getGroup());
        $this->assertEmpty($user->getClass());
        $this->assertEmpty($user->getDisplayName());
        $this->assertEmpty($user->getDir());
        $user->setData(123, 'lkjh', 800, 900, 12, 'DsFh', 'noooone');
        $this->assertEquals(123, $user->getAuthId());
        $this->assertEquals('lkjh', $user->getAuthName());
        $this->assertEquals(800, $user->getGroup());
        $this->assertEquals(900, $user->getClass());
        $this->assertEquals(12, $user->getStatus());
        $this->assertEquals('DsFh', $user->getDisplayName());
        $this->assertEquals('noooone', $user->getDir());
    }

    public function testGroup(): void
    {
        $user = new Data\FileGroup();
        $this->assertEmpty($user->getGroupId());
        $this->assertEmpty($user->getGroupName());
        $this->assertEmpty($user->getGroupAuthorId());
        $this->assertEmpty($user->getGroupDesc());
        $user->setData(987, 'lkjh', 800, 'watwat', 5);
        $this->assertEquals(987, $user->getGroupId());
        $this->assertEquals('lkjh', $user->getGroupName());
        $this->assertEquals(800, $user->getGroupAuthorId());
        $this->assertEquals('watwat', $user->getGroupDesc());
        $this->assertEquals(5, $user->getGroupStatus());
    }

    public function testCertUser(): void
    {
        $user = new Data\FileCertUser();
        $this->assertEmpty($user->getPubKey());
        $this->assertEmpty($user->getPubSalt());
        $user->addCertInfo('asdfghjkl', 'once_none');
        $this->assertEquals('asdfghjkl', $user->getPubKey());
        $this->assertEquals('once_none', $user->getPubSalt());
    }
}
