<?php

namespace kalanis\kw_auth\Sources\Mapper\Database;


use kalanis\kw_auth\Interfaces\IGroup;
use kalanis\kw_auth\Traits\TSeparated;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\ASimpleRecord;


/**
 * Class GroupsRecord
 * @package kalanis\kw_auth\Sources\Mapper\Database
 * @property string $id
 * @property string $name
 * @property string $desc
 * @property string $authorId
 * @property string $parents
 * @property int $status
 * @property UsersRecord[] $authors
 * @property UsersRecord[] $members
 * @codeCoverageIgnore remote source
 */
class GroupsRecord extends ASimpleRecord implements IGroup
{
    use TSeparated;

    public function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_STRING, 2048);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('desc', IEntryType::TYPE_STRING, 512);
        $this->addEntry('authorId', IEntryType::TYPE_STRING, 128);
        $this->addEntry('parents', IEntryType::TYPE_STRING, 2048);
        $this->addEntry('status', IEntryType::TYPE_INTEGER, 4);
        $this->addEntry('authors', IEntryType::TYPE_ARRAY, []);
        $this->addEntry('members', IEntryType::TYPE_ARRAY, []);
        $this->setMapper(GroupsMapper::class);
    }

    public function setGroupData(?string $id, ?string $name, ?string $desc, ?string $authorId, ?int $status, ?array $parents = []): void
    {
        $this->id = $id ?? $this->id;
        $this->name = $name ?? $this->name;
        $this->desc = $desc ?? $this->desc;
        $this->authorId = $authorId ?? $this->authorId;
        $this->status = $status ?? $this->status;
        $this->parents = $parents ? $this->compactStr($parents) : $this->parents;
    }

    public function getGroupId(): string
    {
        return strval($this->id);
    }

    public function getGroupName(): string
    {
        return strval($this->name);
    }

    public function getGroupDesc(): string
    {
        return strval($this->desc);
    }

    public function getGroupAuthorId(): string
    {
        return strval($this->authorId);
    }

    public function getGroupStatus(): int
    {
        return intval($this->status);
    }

    public function getGroupParents(): array
    {
        return $this->separateStr($this->parents);
    }
}
