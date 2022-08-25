<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;


/**
 * Interface IMode
 * @package kalanis\kw_auth\Interfaces
 * Mode for testing hashes
 */
interface IMode
{
    /**
     * Check itself
     * @param string $pass
     * @param string $hash
     * @throws AuthException
     * @return bool
     */
    public function check(string $pass, string $hash): bool;

    /**
     * Create new one
     * @param string $pass
     * @param string|null $method
     * @throws AuthException
     * @return string
     */
    public function hash(string $pass, ?string $method = null): string;
}
