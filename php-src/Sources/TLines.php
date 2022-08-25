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
    /**
     * @param string $input
     * @return array<int, string>
     */
    public function explosion(string $input): array
    {
        return explode(IFile::SEPARATOR, $input);
    }

    /**
     * @param array<int, string|int|float> $input
     * @return string
     */
    public function implosion(array $input): string
    {
        return implode(IFile::SEPARATOR, $input + ['']);
    }

    public function stripChars(string $input): string
    {
        return strval(preg_replace('#[^a-zA-Z0-9\,\*\/\.\-\+\?\_\§\"\!\/\(\)\|\€\'\\\&\@\{\}\<\>\#\ ]#', '', $input));
    }
}
