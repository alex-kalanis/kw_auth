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
    /**
     * @param IGroup $group
     * @throws AuthException
     * @throws LockException
     */
    public function createGroup(IGroup $group): void;

    /**
     * @param int $groupId
     * @return IGroup|null
     * @throws AuthException
     * @throws LockException
     */
    public function getGroupDataOnly(int $groupId): ?IGroup;

    /**
     * @return IGroup[]
     * @throws AuthException
     * @throws LockException
     */
    public function readGroup(): array;

    /**
     * @param IGroup $group
     * @throws AuthException
     * @throws LockException
     */
    public function updateGroup(IGroup $group): void;

    /**
     * @param int $groupId
     * @throws AuthException
     * @throws LockException
     */
    public function deleteGroup(int $groupId): void;
}
