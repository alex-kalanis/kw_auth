<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IFile;


/**
 * Trait TFiles
 * @package kalanis\kw_auth\Sources
 * Processing files with accounts
 */
trait TFiles
{
    /**
     * @param string $path
     * @return string[][]
     * @throws AuthException
     */
    protected function openFile(string $path): array
    {
        $content = @file($path);
        if (false === $content) {
            throw new AuthException('File with passwords not found in preselect path');
        }
        return array_map([$this, 'explosion'], array_map('trim', $content));
    }

    abstract public function explosion(string $input): array;

    /**
     * @param string $path
     * @param string[][] $lines
     * @throws AuthException
     */
    protected function saveFile(string $path, array $lines): void
    {
        $content = implode(IFile::CRLF, array_map([$this, 'implosion'], $lines)) . IFile::CRLF;
        $result = @file_put_contents($path, $content);
        if (false === $result) {
            throw new AuthException('File with passwords cannot be saved in preselect path');
        }
    }

    abstract public function implosion(array $input): string;
}
