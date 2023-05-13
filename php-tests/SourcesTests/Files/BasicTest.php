<?php

namespace SourcesTests\Files;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\TExpire;
use kalanis\kw_auth\Interfaces\IExpire;
use kalanis\kw_auth\Sources;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


class BasicTest extends CommonTestClass
{
    /**
     * @param string $in
     * @param string $want
     * @dataProvider stripProvider
     */
    public function testStrip(string $in, string $want): void
    {
        $lib = new MockLines();
        $this->assertEquals($want, $lib->stripChars($in));
    }

    public function stripProvider(): array
    {
        return [
            ['yxcvbnm', 'yxcvbnm'],
            ['jk~l,.qđwĐe', 'jkl,.qwe'],
        ];
    }

    /**
     * @param string $in
     * @param string $want
     * @dataProvider exImProvider
     */
    public function testExIm(string $in, string $want): void
    {
        $lib = new MockLines();
        $this->assertEquals($want, $lib->implosion($lib->explosion($in)));
    }

    public function exImProvider(): array
    {
        return [
            ['yxc:vb:nm', 'yxc:vb:nm'],
            ['yxcvbnm', 'yxcvbnm'],
            ['j°k~l:,.qđwĐe', 'j°k~l:,.qđwĐe'],
        ];
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testLockEmpty(): void
    {
        $lib = new MockAuthLock(null);
        $this->expectException(AuthException::class);
        $lib->check();
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testLockSimple(): void
    {
        $lib = new MockAuthLock($this->getLockPath());
        $lib->check();
        $this->assertTrue(true); // it runs, no errors
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testLockMix(): void
    {
        $lock = $this->getLockPath();
        $lib = new MockAuthLock($lock);
        $lock->create();
        $this->expectException(AuthException::class);
        $lib->check();
    }

    /**
     * @throws AuthException
     */
    public function testExpire(): void
    {
        $target = new Expire();
        $lib = new MockExpiration(700, 100);
        $this->assertFalse($target->willExpire());

        $lib->setExpirationNotice($target, 650);
        $this->assertTrue($target->willExpire());

        $lib->setExpirationNotice($target, 750);
        $this->assertFalse($target->willExpire());

        $lib->updateExpirationTime($target);
        $this->assertEquals(1350, $target->getExpireTime());
    }
}


class MockLines
{
    use Sources\Files\TLines;
}


class MockAuthLock
{
    use Sources\TAuthLock;

    public function __construct(?ILock $lock)
    {
        $this->initAuthLock($lock);
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function check(): void
    {
        $this->checkLock();
    }
}


class MockExpiration
{
    use Sources\TExpiration;

    public function __construct(int $changeInterval, int $changeNoticeBefore)
    {
        $this->initExpiry($changeInterval, $changeNoticeBefore);
    }

    protected function getTime(): int
    {
        return 650;
    }
}


class Expire implements IExpire
{
    use TExpire;
}
