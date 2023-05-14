<?php

namespace kalanis\kw_auth\Sources\Files\Files;


use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Sources\Files\AGroups;
use kalanis\kw_files\Access\CompositeAdapter;
use kalanis\kw_locks\Interfaces\ILock;


/**
 * Class Groups
 * @package kalanis\kw_auth\Sources\Files\Files
 * Authenticate via files - manage groups
 */
class Groups extends AGroups
{
    use TFiles;

    /**
     * @param CompositeAdapter $files
     * @param ILock $lock
     * @param string[] $path
     * @param IKauTranslations|null $lang
     */
    public function __construct(CompositeAdapter $files, ILock $lock, array $path, ?IKauTranslations $lang = null)
    {
        $this->files = $files;
        parent::__construct($lock, $path, $lang);
    }
}
