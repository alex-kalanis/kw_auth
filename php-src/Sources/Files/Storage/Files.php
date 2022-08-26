<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Sources\Files\AFiles;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_storage\Storage\Storage;


/**
 * Class Files
 * @package kalanis\kw_auth\Sources\Files\Storage
 * Authenticate via multiple files
 */
class Files extends AFiles
{
    use TStorage;

    public function __construct(Storage $storage, IMode $mode, ILock $lock, string $dir, ?IKATranslations $lang = null)
    {
        $this->storage = $storage;
        parent::__construct($mode, $lock, $dir, $lang);
    }
}
