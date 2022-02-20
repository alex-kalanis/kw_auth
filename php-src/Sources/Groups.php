<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAccessGroups;
use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_locks\Interfaces\ILock;


/**
 * Class Groups
 * @package kalanis\kw_auth\Sources
 * Authenticate via files - manage groups
 */
class Groups extends AFile implements IAccessGroups
{
    use TGroups;

    /**
     * @param ILock $lock
     * @param string $path full path to group file
     * @param IKATranslations|null $lang
     */
    public function __construct(ILock $lock, string $path, ?IKATranslations $lang = null)
    {
        $this->setLang($lang);
        $this->initAuthLock($lock);
        $this->path = $path;
    }

    protected function checkRest(int $groupId): void
    {
        // nothing here
    }

    /**
     * @return string[][]
     * @throws AuthException
     */
    protected function openGroups(): array
    {
        return $this->openFile($this->path);
    }

    /**
     * @param string[][] $lines
     * @throws AuthException
     */
    protected function saveGroups(array $lines): void
    {
        $this->saveFile($this->path, $lines);
    }
}
