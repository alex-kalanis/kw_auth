<?php

namespace kalanis\kw_auth\Sources\Files\Volume;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\TTranslate;


/**
 * Trait TFiles
 * @package kalanis\kw_auth\Sources\Files\Volume
 * Processing files with accounts
 */
trait TVolume
{
    use TTranslate;

    /**
     * @param string $path
     * @throws AuthException
     * @return array<int, array<int, string>>
     */
    protected function openFile(string $path): array
    {
        $content = @file($path);
        if (false === $content) {
            throw new AuthException($this->getLang()->kauPassFileNotFound($path));
        }
        return array_map([$this, 'explosion'], array_filter(array_map('trim', $content), [$this, 'filterEmptyLines']));
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
        $result = @file_put_contents($path, $content);
        if (false === $result) {
            throw new AuthException($this->getLang()->kauPassFileNotSave($path));
        }
    }

    abstract public function implosion(array $input): string;
}
