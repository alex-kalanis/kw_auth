<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\Interfaces\IFile;


/**
 * Trait TLines
 * @package kalanis\kw_auth\Sources
 * Processing lines of accounts in files
 */
trait TLines
{
    public function explosion(string $input): array
    {
        return explode(IFile::SEPARATOR, $input);
    }

    public function implosion(array $input): string
    {
        return implode(IFile::SEPARATOR, $input + ['']);
    }

    protected function stripChars(string $input): string
    {
        return preg_replace('#[^a-zA-Z0-9\,\*\/\.\-\+\?\_\§\"\!\/\(\)\|\€\'\&\@\{\}\<\>\#\\\]#', '', $input);
    }
}
