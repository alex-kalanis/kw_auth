<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Interfaces\IAccessGroups;
use kalanis\kw_auth\Interfaces\IGroup;
use kalanis\kw_locks\LockException;


/**
 * Trait TGroups
 * @package kalanis\kw_auth\Sources
 * Work with groups
 */
trait TGroups
{
    use TAuthLock;
    use TLines;

    /**
     * @param IGroup $group
     * @throws AuthException
     * @throws LockException
     */
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
        $groupLines = $this->openGroups();
        foreach ($groupLines as &$line) {
            $gid = max($gid, $line[IAccessGroups::GRP_ID]);
        }
        $gid++;

        $newGroup = [
            IAccessGroups::GRP_ID => $gid,
            IAccessGroups::GRP_NAME => $groupName,
            IAccessGroups::GRP_AUTHOR => $userId,
            IAccessGroups::GRP_DESC => !empty($groupDesc) ? $groupDesc : $groupName,
            IAccessGroups::GRP_FEED => '',
        ];
        ksort($newGroup);
        $groupLines[] = $newGroup;

        # now save it
        $this->saveGroups($groupLines);

        $this->lock->delete();
    }

    /**
     * @param int $groupId
     * @return IGroup|null
     * @throws AuthException
     * @throws LockException
     */
    public function getGroupDataOnly(int $groupId): ?IGroup
    {
        $this->checkLock();
        $groupLines = $this->openGroups();
        foreach ($groupLines as &$line) {
            if ($line[IAccessGroups::GRP_ID] == $groupId) {
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

        $groupLines = $this->openGroups();
        $result = [];
        foreach ($groupLines as &$line) {
            $result[] = $this->getGroupClass($line);
        }

        return $result;
    }

    /**
     * @param array $line
     * @return IGroup
     */
    protected function getGroupClass(array &$line): IGroup
    {
        $group = new FileGroup();
        $group->setData(
            intval($line[IAccessGroups::GRP_ID]),
            strval($line[IAccessGroups::GRP_NAME]),
            intval($line[IAccessGroups::GRP_AUTHOR]),
            strval($line[IAccessGroups::GRP_DESC])
        );
        return $group;
    }

    /**
     * @param IGroup $group
     * @throws AuthException
     * @throws LockException
     */
    public function updateGroup(IGroup $group): void
    {
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        $this->checkLock();

        $this->lock->create();
        $groupLines = $this->openGroups();
        foreach ($groupLines as &$line) {
            if ($line[IAccessGroups::GRP_ID] == $group->getGroupId()) {
                // REFILL
                $line[IAccessGroups::GRP_NAME] = !empty($groupName) ? $groupName : $line[IAccessGroups::GRP_NAME] ;
                $line[IAccessGroups::GRP_DESC] = !empty($groupDesc) ? $groupDesc : $line[IAccessGroups::GRP_DESC] ;
            }
        }

        $this->saveGroups($groupLines);
        $this->lock->delete();
    }

    /**
     * @param int $groupId
     * @throws AuthException
     * @throws LockException
     */
    public function deleteGroup(int $groupId): void
    {
        $this->checkLock();
        $this->checkRest($groupId);

        $changed = false;
        $this->lock->create();

        # update groups
        $openGroups = $this->openGroups();
        foreach ($openGroups as $index => &$line) {
            if ($line[IAccessGroups::GRP_ID] == $groupId) {
                unset($openGroups[$index]);
                $changed = true;
            }
        }

        # now save it
        if ($changed) {
            $this->saveGroups($openGroups);
        }
        $this->lock->delete();
    }

    /**
     * Check the rest of source for existence of group
     * @param int $groupId
     * @throws AuthException
     */
    abstract protected function checkRest(int $groupId): void;

    /**
     * @return string[][]
     * @throws AuthException
     */
    abstract protected function openGroups(): array;

    /**
     * @param string[][] $lines
     * @throws AuthException
     */
    abstract protected function saveGroups(array $lines): void;
}
