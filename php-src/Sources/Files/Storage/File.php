<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Sources\Files\AFile;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_storage\Interfaces\IStorage;


/**
 * Class File
 * @package kalanis\kw_auth\Sources\Files\Storage
 * Authenticate via single file
 */
class File extends AFile
{
    use TStorage;

    /**
     * @param IStorage $storage where to save
     * @param IMode $mode hashing mode
     * @param ILock $lock file lock
     * @param string $path use full path with file name
     * @param IKATranslations|null $lang
     */
    public function __construct(IStorage $storage, IMode $mode, ILock $lock, string $path, ?IKATranslations $lang = null)
    {
        $this->storage = $storage;
        parent::__construct($mode, $lock, $path, $lang);
    }
}
