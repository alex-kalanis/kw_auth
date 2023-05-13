<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;
use kalanis\kw_locks\LockException;


/**
 * Interface IAccessGroups
 * @package kalanis\kw_auth\Interfaces
 * Accessing groups manipulation
 * They are set against each user and say what is allowed to access
 */
interface IAccessGroups
{
    // default positions
    const GRP_ID = 0;
    const GRP_NAME = 1;
    const GRP_AUTHOR = 2;
    const GRP_DESC = 3;
    const GRP_STATUS = 4;
    const GRP_PARENTS = 5;
    const GRP_FEED = 6;

    /**
     * @param IGroup $group
     * @throws AuthException
     * @throws LockException
     */
    public function createGroup(IGroup $group): void;

    /**
     * @param int $groupId
     * @throws AuthException
     * @throws LockException
     * @return IGroup|null
     */
    public function getGroupDataOnly(int $groupId): ?IGroup;

    /**
     * @throws AuthException
     * @throws LockException
     * @return IGroup[]
     */
    public function readGroup(): array;

    /**
     * @param IGroup $group
     * @throws AuthException
     * @throws LockException
     * @return bool
     */
    public function updateGroup(IGroup $group): bool;

    /**
     * @param int $groupId
     * @throws AuthException
     * @throws LockException
     * @return bool
     */
    public function deleteGroup(int $groupId): bool;
}
