<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\TTranslate;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;


/**
 * Trait TStorage
 * @package kalanis\kw_auth\Sources\Files\Storage
 * Processing files with accounts
 */
trait TStorage
{
    use TTranslate;

    /** @var IStorage */
    protected $storage = null;

    /**
     * @param string $path
     * @throws AuthException
     * @return array<int, array<int, string>>
     */
    protected function openFile(string $path): array
    {
        try {
            $content = $this->storage->read($path);
            $lines = explode(IFile::CRLF, strval($content));
            return array_map([$this, 'explosion'], array_filter(array_map('trim', $lines), [$this, 'filterEmptyLines']));
        } catch (StorageException $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    abstract public function explosion(string $input): array;

    abstract public function filterEmptyLines(string $input): bool;

    /**
     * @param string $path
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthException
     */
    protected function saveFile(string $path, array $lines): void
    {
        $content = implode(IFile::CRLF, array_map([$this, 'implosion'], $lines)) . IFile::CRLF;
        try {
            if (false === $this->storage->write($path, $content)) {
                throw new AuthException($this->getLang()->kauPassFileNotSave($path));
            }
        } catch (StorageException $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    abstract public function implosion(array $input): string;
}
