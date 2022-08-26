<?php

namespace SourcesTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\TExpire;
use kalanis\kw_auth\Interfaces\IExpire;
use kalanis\kw_auth\Sources;
use kalanis\kw_auth\Translations;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


class BasicTest extends CommonTestClass
{
    protected $sourcePath = '';
    protected $testingPath = '';

    protected function setUp(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '.groups';
        $this->testingPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '.groups-duplicate';
    }

    protected function tearDown(): void
    {
        if (is_file($this->testingPath)) {
            unlink($this->testingPath);
        }
    }

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
     */
    public function testFiles(): void
    {
        $lib = new MockFiles();
        $lib->setLang(new Translations());
        $content = $lib->open($this->sourcePath);
        $this->assertNotEmpty($content);
        $lib->save($this->testingPath, $content);
        chmod($this->testingPath, 0444);
        $this->expectException(AuthException::class);
        $lib->save($this->testingPath, $content);
    }

    /**
     * @throws AuthException
     */
    public function testFilesOpenCrash(): void
    {
        $lib = new MockFiles();
        $lib->setLang(new Translations());
        $this->expectException(AuthException::class);
        $lib->open($this->testingPath);
    }

    public function testClasses(): void
    {
        $lib = new Sources\Classes();
        $data = $lib->readClasses();
        $this->assertEquals('Maintainer', $data[1]);
        $this->assertEquals('User', $data[3]);
    }

    public function testLockEmpty(): void
    {
        $lib = new MockAuthLock(null);
        $lib->setLang(new Translations());
        $this->expectException(AuthException::class);
        $lib->check();
    }

    /**
     * @throws LockException
     */
    public function testLockSimple(): void
    {
        $lib = new MockAuthLock($this->getLockPath());
        $lib->setLang(new Translations());
        $lib->check();
        $this->assertTrue(true); // it runs, no errors
    }

    /**
     * @throws LockException
     */
    public function testLockMix(): void
    {
        $lock = $this->getLockPath();
        $lib = new MockAuthLock($lock);
        $lib->setLang(new Translations());
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


class MockFiles
{
    use Sources\Files\Volume\TVolume;
    use Sources\Files\TLines;

    /**
     * @param string $path
     * @throws AuthException
     * @return string[][]
     */
    public function open(string $path): array
    {
        return $this->openFile($path);
    }

    /**
     * @param string $path
     * @param string[][] $content
     * @throws AuthException
     */
    public function save(string $path, array $content): void
    {
        $this->saveFile($path, $content);
    }
}


class MockAuthLock
{
    use Sources\TAuthLock;

    public function __construct(?ILock $lock)
    {
        $this->initAuthLock($lock);
    }

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
