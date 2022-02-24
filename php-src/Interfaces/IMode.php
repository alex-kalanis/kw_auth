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
     * @return bool
     * @throws AuthException
     */
    public function check(string $pass, string $hash): bool;

    /**
     * Create new one
     * @param string $pass
     * @param string|null $method
     * @return string
     * @throws AuthException
     */
    public function hash(string $pass, ?string $method = null): string;
}
