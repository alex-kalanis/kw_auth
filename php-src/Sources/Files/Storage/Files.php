<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Interfaces\IStatus;
use kalanis\kw_auth\Sources\Files\AFiles;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_storage\Interfaces\IStorage;


/**
 * Class Files
 * @package kalanis\kw_auth\Sources\Files\Storage
 * Authenticate via multiple files
 */
class Files extends AFiles
{
    use TStorage;

    /**
     * @param IStorage $storage
     * @param IMode $mode
     * @param IStatus $status
     * @param ILock $lock
     * @param string[] $dir
     * @param IKauTranslations|null $lang
     */
    public function __construct(IStorage $storage, IMode $mode, IStatus $status, ILock $lock, array $dir, ?IKauTranslations $lang = null)
    {
        $this->storage = $storage;
        parent::__construct($mode, $status, $lock, $dir, $lang);
    }
}
