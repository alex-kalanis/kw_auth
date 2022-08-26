<?php

namespace SourcesTests\Files\Storage;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Sources;
use kalanis\kw_auth\Translations;
use kalanis\kw_storage\Storage\Key\DefaultKey;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target\Volume;


class BasicTest extends CommonTestClass
{
    protected $sourcePath = '';
    protected $testingPath = '';

    protected function setUp(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '.groups';
        $this->testingPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '.groups-duplicate';
    }

    protected function tearDown(): void
    {
        if (is_file($this->testingPath)) {
            unlink($this->testingPath);
        }
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
}


class MockFiles
{
    use Sources\Files\Storage\TStorage;
    use Sources\Files\TLines;

    public function __construct()
    {
        $this->storage = new Storage(new DefaultKey(), new Volume());
    }

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
