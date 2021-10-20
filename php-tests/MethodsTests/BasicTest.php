<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods;
use kalanis\kw_auth\Sources\TFiles;
use kalanis\kw_auth\Sources\TLines;


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
     * @throws \kalanis\kw_auth\AuthException
     * @throws \kalanis\kw_locks\LockException
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

    public function testFiles(): void
    {
        $lib = new MockFiles();
        $content = $lib->open($this->sourcePath);
        $this->assertnotEmpty($content);
        $lib->save($this->testingPath, $content);
        $this->expectException(AuthException::class);
        $lib->save($this->testingPath, $content);
    }

    public function testFilesOpenCrash(): void
    {
        $lib = new MockFiles();
        $this->expectException(AuthException::class);
        $lib->open($this->testingPath);
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


class MockLines
{
    use TLines;
}


class MockFiles
{
    use TFiles;
    use TLines;

    public function open(string $path): array
    {
        return $this->openFile($path);
    }

    public function save(string $path, array $content): void
    {
        $this->saveFile($path, $content);
    }
}
