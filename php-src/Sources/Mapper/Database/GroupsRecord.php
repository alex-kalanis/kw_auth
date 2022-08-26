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
 * @property int $authorId
 * @property UsersRecord[] $authors
 * @property UsersRecord[] $members
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
        $this->addEntry('authors', IEntryType::TYPE_ARRAY, []);
        $this->addEntry('members', IEntryType::TYPE_ARRAY, []);
        $this->setMapper('\kalanis\kw_auth\Sources\Mapper\Database\UsersMapper');
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
}
