<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Traits\TLang;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Trait TAuthLock
 * @package kalanis\kw_auth\Sources
 */
trait TAuthLock
{
    use TLang;

    /** @var ILock|null */
    protected $lock = null;

    protected function initAuthLock(?ILock $lock): void
    {
        $this->lock = $lock;
    }

    /**
     * @throws AuthException
     * @return ILock
     */
    protected function getLock(): ILock
    {
        if (!$this->lock) {
            throw new AuthException($this->getAuLang()->kauLockSystemNotSet());
        }
        return $this->lock;
    }

    /**
     * @param string $note
     * @throws AuthException
     * @throws LockException
     */
    protected function checkLock(string $note = ''): void
    {
        if ($this->getLock()->has()) {
            throw new AuthException(empty($note) ? $this->getAuLang()->kauAuthAlreadyOpen() : $note);
        }
    }
}
