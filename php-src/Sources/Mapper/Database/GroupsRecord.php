<?php

namespace kalanis\kw_auth\Sources\Mapper\Database;


use kalanis\kw_auth\Interfaces\IGroup;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\ASimpleRecord;


/**
 * Class GroupsRecord
 * @package kalanis\kw_auth\Sources\Mapper\Database
 * @property int $id
 * @property string $name
 * @property string $desc
 * @property int $status
 * @property int $authorId
 * @property int $parentId
 * @property UsersRecord[] $authors
 * @property UsersRecord[] $members
 * @property GroupsRecord[] $parents
 * @codeCoverageIgnore remote source
 */
class GroupsRecord extends ASimpleRecord implements IGroup
{
    public function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('name', IEntryType::TYPE_STRING, 512);
        $this->addEntry('desc', IEntryType::TYPE_STRING, 512);
        $this->addEntry('authorId', IEntryType::TYPE_INTEGER, 128);
        $this->addEntry('parentId', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('status', IEntryType::TYPE_INTEGER, 4);
        $this->addEntry('authors', IEntryType::TYPE_ARRAY, []);
        $this->addEntry('members', IEntryType::TYPE_ARRAY, []);
        $this->addEntry('parents', IEntryType::TYPE_ARRAY, []);
        $this->setMapper(GroupsMapper::class);
    }

    public function getGroupId(): int
    {
        return intval($this->id);
    }

    public function getGroupName(): string
    {
        return strval($this->name);
    }

    public function getGroupDesc(): string
    {
        return strval($this->desc);
    }

    public function getGroupAuthorId(): int
    {
        return intval($this->authorId);
    }

    public function getGroupStatus(): int
    {
        return intval($this->status);
    }

    public function getGroupParents(): array
    {
        return array_map(function (GroupsRecord $record) {
            return intval($record->id);
        }, $this->parents);
    }
}
