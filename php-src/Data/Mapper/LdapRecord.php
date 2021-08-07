<?php

namespace kalanis\kw_auth\Data\Mapper;


use kalanis\kw_auth\Interfaces\IAccessClasses;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\ASimpleRecord;


/**
 * Class LdapRecord
 * @package kalanis\kw_auth\Sources\Mapper
 * @property int id
 * @property string name
 */
class LdapRecord extends ASimpleRecord implements IUser
{
    protected function setMap(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('name', IEntryType::TYPE_STRING, 128);
        $this->setMapper('\kalanis\kw_auth\Sources\Mapper\LdapMapper');
    }

    public function setData(int $authId, string $authName, int $authGroup, int $authClass, string $displayName, string $dir): void
    {
        // load data only from ldap!
    }

    public function getAuthId(): int
    {
        return (int)$this->id;
    }

    public function getAuthName(): string
    {
        return (string)$this->name;
    }

    public function getGroup(): int
    {
        return IUser::LOWEST_USER_ID;
    }

    public function getClass(): int
    {
        return IAccessClasses::CLASS_USER;
    }

    public function getDisplayName(): string
    {
        return (string)$this->name;
    }

    public function getDir(): string
    {
        return '/';
    }
}
