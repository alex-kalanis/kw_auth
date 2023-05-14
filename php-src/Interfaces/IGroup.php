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
     * Fill group; null values will not change
     * @param string|null $id
     * @param string|null $name
     * @param string|null $desc
     * @param string|null $authorId
     * @param int|null $status
     * @param string[]|null $parents
     */
    public function setGroupData(?string $id, ?string $name, ?string $desc, ?string $authorId, ?int $status, ?array $parents = []): void;

    /**
     * ID of group
     * @return string
     */
    public function getGroupId(): string;

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
     * @return string
     */
    public function getGroupAuthorId(): string;

    /**
     * User statuses as defined in \kalanis\kw_auth\Interfaces\IUser
     * @return int
     */
    public function getGroupStatus(): int;

    /**
     * IDs of parent groups
     * @return string[]
     */
    public function getGroupParents(): array;
}
