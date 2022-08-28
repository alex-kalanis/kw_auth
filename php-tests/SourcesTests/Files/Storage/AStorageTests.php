<?php

namespace SourcesTests\Files\Storage;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Sources;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\Storage\Key\DefaultKey;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target\Volume;
use kalanis\kw_storage\StorageException;
use Traversable;


abstract class AStorageTests extends CommonTestClass
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
}


class MockFiles
{
    use Sources\Files\Storage\TStorage;
    use Sources\Files\TLines;

    public function __construct(?IStorage $storage = null)
    {
        $this->storage = new Storage(new DefaultKey(), $storage ?: new Volume());
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


class XCrashStorage implements IStorage
{
    public function check(string $key): bool
    {
        return false;
    }

    public function exists(string $key): bool
    {
        return false;
    }

    public function load(string $key)
    {
        throw new StorageException('nope');
    }

    public function save(string $key, $data, ?int $timeout = null): bool
    {
        throw new StorageException('nope');
    }

    public function remove(string $key): bool
    {
        throw new StorageException('nope');
    }

    public function lookup(string $path): Traversable
    {
        throw new StorageException('nope');
    }

    public function increment(string $key): bool
    {
        throw new StorageException('nope');
    }

    public function decrement(string $key): bool
    {
        throw new StorageException('nope');
    }

    public function removeMulti(array $keys): array
    {
        throw new StorageException('nope');
    }
}
