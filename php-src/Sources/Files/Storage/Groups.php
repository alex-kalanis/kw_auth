<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_auth\Sources\Files\AGroups;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_storage\Storage\Storage;


/**
 * Class Groups
 * @package kalanis\kw_auth\Sources\Files\Storage
 * Authenticate via files - manage groups
 */
class Groups extends AGroups
{
    use TStorage;

    public function __construct(Storage $storage, ILock $lock, string $path, ?IKATranslations $lang = null)
    {
        $this->storage = $storage;
        parent::__construct($lock, $path, $lang);
    }
}
