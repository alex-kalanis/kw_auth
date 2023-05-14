<?php

namespace kalanis\kw_auth\Sources\Files\Volume;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\Traits\TLang;
use kalanis\kw_paths\PathsException;
use kalanis\kw_paths\Stuff;


/**
 * Trait TVolume
 * @package kalanis\kw_auth\Sources\Files\Volume
 * Processing files with accounts
 */
trait TVolume
{
    use TLang;

    /**
     * @param string[] $path
     * @throws AuthException
     * @return array<int, array<int, string>>
     */
    protected function openFile(array $path): array
    {
        try {
            $pt = Stuff::arrayToPath($path);
            $content = @file($pt);
            if (false === $content) {
                throw new AuthException($this->getAuLang()->kauPassFileNotFound($pt));
            }
            return array_map([$this, 'explosion'], array_filter(array_map('trim', $content), [$this, 'filterEmptyLines']));
        } catch (PathsException $ex) {
            // @codeCoverageIgnoreStart
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
        // @codeCoverageIgnoreEnd
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
        try {
            $content = implode(IFile::CRLF, array_map([$this, 'implosion'], $lines)) . IFile::CRLF;
            $pt = Stuff::arrayToPath($path);
            $result = @file_put_contents($pt, $content);
            if (false === $result) {
                throw new AuthException($this->getAuLang()->kauPassFileNotSave($pt));
            }
        } catch (PathsException $ex) {
            // @codeCoverageIgnoreStart
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
        // @codeCoverageIgnoreEnd
    }

    abstract public function implosion(array $input): string;
}
