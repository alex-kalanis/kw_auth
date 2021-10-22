<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Trait TAuthLock
 * @package kalanis\kw_auth\Sources
 */
trait TAuthLock
{
    /** @var ILock|null */
    protected $lock = null;

    protected function initAuthLock(?ILock $lock): void
    {
        $this->lock = $lock;
    }

    /**
     * @param string $note
     * @throws AuthException
     * @throws LockException
     */
    protected function checkLock(string $note = 'Someone works with authentication. Please try again a bit later.'): void
    {
        if (!$this->lock) {
            throw new AuthException('Lock system not set');
        }
        if ($this->lock->has()) {
            throw new AuthException($note);
        }
    }
}
