<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Interfaces\IStatus;
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
     * @param IStatus $status which status is necessary to use that feature
     * @param ILock $lock file lock
     * @param string[] $path use full path with file name
     * @param IKauTranslations|null $lang
     */
    public function __construct(IStorage $storage, IMode $mode, IStatus $status, ILock $lock, array $path, ?IKauTranslations $lang = null)
    {
        $this->storage = $storage;
        parent::__construct($mode, $status, $lock, $path, $lang);
    }
}
