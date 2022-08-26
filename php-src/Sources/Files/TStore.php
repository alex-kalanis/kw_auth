<?php

namespace kalanis\kw_auth\Sources\Files;


use kalanis\kw_auth\AuthException;


/**
 * trait TStore
 * @package kalanis\kw_auth\Sources\Files
 * Which storage will be used for accessing accounts in files
 * Trait because these calls should not be accessed from outside
 */
trait TStore
{
    /**
     * @param string $path
     * @throws AuthException
     * @return array<int, array<int, string>>
     */
    abstract protected function openFile(string $path): array;

    /**
     * @param string $path
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthException
     */
    abstract protected function saveFile(string $path, array $lines): void;
}
