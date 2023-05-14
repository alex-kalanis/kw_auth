<?php

namespace SourcesTests\Files\Files;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Sources;
use kalanis\kw_files\Access;
use kalanis\kw_files\FilesException;
use kalanis\kw_paths\PathsException;
use kalanis\kw_storage\Interfaces\ITarget;
use kalanis\kw_storage\Storage\Key;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target;
use kalanis\kw_storage\StorageException;
use Traversable;


abstract class AFilesTests extends CommonTestClass
{
    protected $sourcePath = '';
    protected $testingPath = '';

    protected function setUp(): void
    {
        $this->sourcePath = '.groups';
        $this->testingPath = '.groups-duplicate';
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
    use Sources\Files\Files\TFiles;
    use Sources\Files\TLines;

    /**
     * @param ITarget|null $storage
     * @throws FilesException
     * @throws PathsException
     */
    public function __construct(?ITarget $storage = null)
    {
        Key\DirKey::setDir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR);
        $this->files = (new Access\Factory())->getClass(new Storage(new Key\DirKey(), $storage ?: new Target\Volume()));
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


class XCrashStorage implements ITarget
{
    public function check(string $key): bool
    {
        return false;
    }

    public function exists(string $key): bool
    {
        throw new StorageException('nope');
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
