<?php

namespace SourcesTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Sources\Classes;
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
        $content = $lib->open($this->sourcePath);
        $this->assertNotEmpty($content);
        $lib->save($this->testingPath, $content);
        $this->expectException(AuthException::class);
        $lib->save($this->testingPath, $content);
    }

    /**
     * @throws AuthException
     */
    public function testFilesOpenCrash(): void
    {
        $lib = new MockFiles();
        $this->expectException(AuthException::class);
        $lib->open($this->testingPath);
    }

    public function testClasses(): void
    {
        $lib = new Classes();
        $data = $lib->readClasses();
        $this->assertEquals('Maintainer', $data[1]);
        $this->assertEquals('User', $data[3]);
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

    /**
     * @param string $path
     * @return string[][]
     * @throws AuthException
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
