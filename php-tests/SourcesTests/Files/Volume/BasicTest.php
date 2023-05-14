<?php

namespace SourcesTests\Files\Volume;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Sources;


class BasicTest extends CommonTestClass
{
    protected $sourcePath = [];
    protected $testingPath = [];

    protected function setUp(): void
    {
        $this->sourcePath = array_merge(explode(DIRECTORY_SEPARATOR, __DIR__), ['..', '..', '..', 'data', '.groups']);
        $this->testingPath = array_merge(explode(DIRECTORY_SEPARATOR, __DIR__), ['..', '..', '..', 'data', '.groups-duplicate']);
    }

    protected function tearDown(): void
    {
        $pt = implode(DIRECTORY_SEPARATOR, $this->testingPath);
        if (is_file($pt)) {
            unlink($pt);
        }
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
        $pt = implode(DIRECTORY_SEPARATOR, $this->testingPath);
        chmod($pt, 0444);
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
}


class MockFiles
{
    use Sources\Files\Volume\TVolume;
    use Sources\Files\TLines;

    /**
     * @param string[] $path
     * @throws AuthException
     * @return string[][]
     */
    public function open(array $path): array
    {
        return $this->openFile($path);
    }

    /**
     * @param string[] $path
     * @param string[][] $content
     * @throws AuthException
     */
    public function save(array $path, array $content): void
    {
        $this->saveFile($path, $content);
    }
}
