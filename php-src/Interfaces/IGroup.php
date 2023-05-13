<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IGroup
 * @package kalanis\kw_auth\Interfaces
 * Group data from your auth system
 */
interface IGroup
{
    /**
     * ID of group
     * @return int
     */
    public function getGroupId(): int;

    /**
     * Human-understandable name
     * @return string
     */
    public function getGroupName(): string;

    /**
     * Description of group
     * @return string
     */
    public function getGroupDesc(): string;

    /**
     * Who adds it
     * @return int
     */
    public function getGroupAuthorId(): int;

    /**
     * User statuses as defined in \kalanis\kw_auth\Interfaces\IUser
     * @return int
     */
    public function getGroupStatus(): int;

    /**
     * IDs of parent groups
     * @return int[]
     */
    public function getGroupParents(): array;
}
