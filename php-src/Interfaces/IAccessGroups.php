<?php

namespace kalanis\kw_auth\Interfaces;


use kalanis\kw_auth\AuthException;


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
     */
    public function createGroup(IGroup $group): void;

    /**
     * @return IGroup[]
     * @throws AuthException
     */
    public function readGroup(): array;

    /**
     * @param IGroup $group
     * @throws AuthException
     */
    public function updateGroup(IGroup $group): void;

    /**
     * @param int $groupId
     * @throws AuthException
     */
    public function deleteGroup(int $groupId): void;
}
