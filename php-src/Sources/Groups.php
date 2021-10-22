<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Interfaces\IAccessGroups;
use kalanis\kw_auth\Interfaces\IGroup;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class Groups
 * @package kalanis\kw_auth\Sources
 * Authenticate via files - manage groups
 */
class Groups extends AFile implements IAccessGroups
{
    use TAuthLock;

    const GRP_ID = 0;
    const GRP_NAME = 1;
    const GRP_AUTHOR = 2;
    const GRP_DESC = 3;
    const GRP_FEED = 4;

    /**
     * @param ILock $lock
     * @param string $path full path to group file
     */
    public function __construct(ILock $lock, string $path)
    {
        $this->initAuthLock($lock);
        $this->path = $path;
    }

    public function createGroup(IGroup $group): void
    {
        $userId = $group->getGroupAuthorId();
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        # no everything need is set
        if (empty($userId) || empty($groupName)) {
            throw new AuthException('MISSING_NECESSARY_PARAMS');
        }
        $this->checkLock();

        $gid = 0;
        $this->lock->create();

        # read groups
        $groupLines = $this->openFile($this->path);
        foreach ($groupLines as &$line) {
            $gid = max($gid, $line[static::GRP_ID]);
        }
        $gid++;

        $newGroup = [
            static::GRP_ID => $gid,
            static::GRP_NAME => $groupName,
            static::GRP_AUTHOR => $userId,
            static::GRP_DESC => !empty($groupDesc) ? $groupDesc : $groupName,
            static::GRP_FEED => '',
        ];
        ksort($newGroup);
        $groupLines[] = $newGroup;

        # now save it
        $this->saveFile($this->path, $groupLines);

        $this->lock->delete();
    }

    public function getGroupDataOnly(int $groupId): ?IGroup
    {
        $this->checkLock();
        $groupLines = $this->openFile($this->path);
        foreach ($groupLines as &$line) {
            if ($line[static::GRP_ID] == $groupId) {
                return $this->getGroupClass($line);
            }
        }
        return null;
    }

    /**
     * @return IGroup[]
     * @throws AuthException
     * @throws LockException
     */
    public function readGroup(): array
    {
        $this->checkLock();

        $groupLines = $this->openFile($this->path);
        $result = [];
        foreach ($groupLines as &$line) {
            $result[] = $this->getGroupClass($line);
        }

        return $result;
    }

    protected function getGroupClass(array &$line): IGroup
    {
        $group = new FileGroup();
        $group->setData(
            intval($line[static::GRP_ID]),
            strval($line[static::GRP_NAME]),
            intval($line[static::GRP_AUTHOR]),
            strval($line[static::GRP_DESC])
        );
        return $group;
    }

    public function updateGroup(IGroup $group): void
    {
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        $this->checkLock();

        $this->lock->create();
        $groupLines = $this->openFile($this->path);
        foreach ($groupLines as &$line) {
            if ($line[static::GRP_ID] == $group->getGroupId()) {
                // REFILL
                $line[static::GRP_NAME] = !empty($groupName) ? $groupName : $line[static::GRP_NAME] ;
                $line[static::GRP_DESC] = !empty($groupDesc) ? $groupDesc : $line[static::GRP_DESC] ;
            }
        }

        $this->saveFile($this->path, $groupLines);
        $this->lock->delete();
    }

    public function deleteGroup(int $groupId): void
    {
        $this->checkLock();

        $changed = false;
        $this->lock->create();

        # update groups
        $openGroups = $this->openFile($this->path);
        foreach ($openGroups as $index => &$line) {
            if ($line[static::GRP_ID] == $groupId) {
                unset($openGroups[$index]);
                $changed = true;
            }
        }

        # now save it
        if ($changed) {
            $this->saveFile($this->path, $openGroups);
        }
        $this->lock->delete();
    }
}
