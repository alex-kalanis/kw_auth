<?php

namespace kalanis\kw_auth\Sources\Files\Files;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\Traits\TLang;
use kalanis\kw_files\Access\CompositeAdapter;
use kalanis\kw_files\FilesException;
use kalanis\kw_paths\PathsException;
use kalanis\kw_paths\Stuff;


/**
 * Trait TFiles
 * @package kalanis\kw_auth\Sources\Files\Files
 * Processing files with accounts
 */
trait TFiles
{
    use TLang;

    /** @var CompositeAdapter */
    protected $files = null;

    /**
     * @param string[] $path
     * @throws AuthException
     * @return array<int, array<int, string>>
     */
    protected function openFile(array $path): array
    {
        try {
            $content = $this->files->readFile($path);
            $lines = explode(IFile::CRLF, strval($content));
            return array_map([$this, 'explosion'], array_filter(array_map('trim', $lines), [$this, 'filterEmptyLines']));
        } catch (FilesException | PathsException $ex) {
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
            if (false === $this->files->saveFile($path, $content)) {
                throw new AuthException($this->getAuLang()->kauPassFileNotSave(Stuff::arrayToPath($path)));
            }
        } catch (FilesException | PathsException $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    abstract public function implosion(array $input): string;
}
