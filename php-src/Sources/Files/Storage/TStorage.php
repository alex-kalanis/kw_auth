<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\Traits\TLang;
use kalanis\kw_paths\PathsException;
use kalanis\kw_paths\Stuff;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;


/**
 * Trait TStorage
 * @package kalanis\kw_auth\Sources\Files\Storage
 * Processing files with accounts
 */
trait TStorage
{
    use TLang;

    /** @var IStorage */
    protected $storage = null;

    /**
     * @param string[] $path
     * @throws AuthException
     * @return array<int, array<int, string>>
     */
    protected function openFile(array $path): array
    {
        try {
            $content = $this->storage->read(Stuff::arrayToPath($path));
            $lines = explode(IFile::CRLF, strval($content));
            return array_map([$this, 'explosion'], array_filter(array_map('trim', $lines), [$this, 'filterEmptyLines']));
        } catch (StorageException | PathsException $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    abstract public function explosion(string $input): array;

    abstract public function filterEmptyLines(string $input): bool;

    /**
     * @param string[] $path
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthException
     */
    protected function saveFile(array $path, array $lines): void
    {
        $content = implode(IFile::CRLF, array_map([$this, 'implosion'], $lines)) . IFile::CRLF;
        try {
            $pt = Stuff::arrayToPath($path);
            if (false === $this->storage->write($pt, $content)) {
                throw new AuthException($this->getAuLang()->kauPassFileNotSave($pt));
            }
        } catch (StorageException | PathsException $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    abstract public function implosion(array $input): string;
}
