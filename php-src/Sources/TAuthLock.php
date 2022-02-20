<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\TTranslate;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Trait TAuthLock
 * @package kalanis\kw_auth\Sources
 */
trait TAuthLock
{
    use TTranslate;

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
    protected function checkLock(string $note = ''): void
    {
        if (!$this->lock) {
            throw new AuthException($this->getLang()->kauLockSystemNotSet());
        }
        if ($this->lock->has()) {
            throw new AuthException(empty($note) ? $this->getLang()->kauAuthAlreadyOpen() : $note);
        }
    }
}
