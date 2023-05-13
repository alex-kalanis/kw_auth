<?php

namespace SourcesTests\Files\Storage;


use kalanis\kw_auth\AuthException;


class BasicTest extends AStorageTests
{
    /**
     * @throws AuthException
     */
    public function testFiles(): void
    {
        $lib = new MockFiles();
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
        $lib = new MockFiles(new XCrashStorage());
        $this->expectException(AuthException::class);
        $lib->open($this->testingPath);
    }

    /**
     * @throws AuthException
     */
    public function testFilesCloseCrash(): void
    {
        $lib = new MockFiles(new XCrashStorage());
        $this->expectException(AuthException::class);
        $lib->save($this->testingPath, [['anything']]);
    }
}
