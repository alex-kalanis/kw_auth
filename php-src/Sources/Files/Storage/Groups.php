<?php

namespace kalanis\kw_auth\Sources\Files\Storage;


use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Sources\Files\AGroups;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_storage\Interfaces\IStorage;


/**
 * Class Groups
 * @package kalanis\kw_auth\Sources\Files\Storage
 * Authenticate via files - manage groups
 */
class Groups extends AGroups
{
    use TStorage;

    /**
     * @param IStorage $storage
     * @param ILock $lock
     * @param string[] $path
     * @param IKauTranslations|null $lang
     */
    public function __construct(IStorage $storage, ILock $lock, array $path, ?IKauTranslations $lang = null)
    {
        $this->storage = $storage;
        parent::__construct($lock, $path, $lang);
    }
}
