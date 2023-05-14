<?php

namespace kalanis\kw_auth\Sources\Mapper\Ldap;


use kalanis\kw_auth\Interfaces\IAccessClasses;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\ASimpleRecord;


/**
 * Class LdapRecord
 * @package kalanis\kw_auth\Sources\Mapper\Ldap
 * @property int $id
 * @property string $name
 * @codeCoverageIgnore remote source
 */
class LdapRecord extends ASimpleRecord implements IUser
{
    public function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('name', IEntryType::TYPE_STRING, 128);
        $this->setMapper(LdapMapper::class);
    }

    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir): void
    {
        // load data only from ldap!
    }

    public function getAuthId(): string
    {
        return (string) $this->id;
    }

    public function getAuthName(): string
    {
        return (string) $this->name;
    }

    public function getGroup(): string
    {
        return IUser::LOWEST_USER_ID;
    }

    public function getClass(): int
    {
        return IAccessClasses::CLASS_USER;
    }

    public function getStatus(): ?int
    {
        return static::USER_STATUS_UNKNOWN;
    }

    public function getDisplayName(): string
    {
        return (string) $this->name;
    }

    public function getDir(): string
    {
        return '/';
    }
}
