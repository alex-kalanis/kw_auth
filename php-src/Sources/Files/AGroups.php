<?php

namespace kalanis\kw_auth\Sources\Files;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAccessGroups;
use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_locks\Interfaces\ILock;


/**
 * Class AGroups
 * @package kalanis\kw_auth\Sources\Files
 * Authenticate via files - manage groups
 */
abstract class AGroups implements IAccessGroups
{
    use TGroups;
    use TLines;
    use TStore;

    /** @var string[] */
    protected $path = [];

    /**
     * @param ILock $lock
     * @param string[] $path full path to group file
     * @param IKauTranslations|null $lang
     */
    public function __construct(ILock $lock, array $path, ?IKauTranslations $lang = null)
    {
        $this->setAuLang($lang);
        $this->initAuthLock($lock);
        $this->path = $path;
    }

    protected function checkRest(/** @scrutinizer ignore-unused */ string $groupId): void
    {
        // nothing here
    }

    /**
     * @throws AuthException
     * @return array<int, array<int, string>>
     */
    protected function openGroups(): array
    {
        return $this->openFile($this->path);
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthException
     */
    protected function saveGroups(array $lines): void
    {
        $this->saveFile($this->path, $lines);
    }
}
