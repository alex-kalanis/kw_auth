<?php

namespace kalanis\kw_auth\Sources\Mapper\Database;


use kalanis\kw_auth\Interfaces\IAccessClasses;
use kalanis\kw_auth\Interfaces\IUserCert;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\ASimpleRecord;


/**
 * Class UsersRecord
 * @package kalanis\kw_auth\Sources\Mapper\Database
 * @property int $id
 * @property string $login
 * @property string $pass
 * @property int $groupId
 * @property string $display
 * @property string $cert
 * @property string $salt
 * @property GroupsRecord[] $groups
 * @codeCoverageIgnore remote source
 */
class UsersRecord extends ASimpleRecord implements IUserCert
{
    public function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_INTEGER, 2048);
        $this->addEntry('login', IEntryType::TYPE_STRING, 512);
        $this->addEntry('pass', IEntryType::TYPE_STRING, 512);
        $this->addEntry('groupId', IEntryType::TYPE_INTEGER, 128);
        $this->addEntry('display', IEntryType::TYPE_STRING, 512);
        $this->addEntry('cert', IEntryType::TYPE_STRING, 8192);
        $this->addEntry('salt', IEntryType::TYPE_STRING, 1024);
        $this->addEntry('groups', IEntryType::TYPE_ARRAY, []);
        $this->setMapper(UsersMapper::class);
    }

    public function setData(int $authId, string $authName, int $authGroup, int $authClass, ?int $authStatus, string $displayName, string $dir): void
    {
        $this->id = $authId;
        $this->load();
        $this->login = $authName;
        $this->groupId = $authGroup;
        $this->display = $displayName;
        $this->save();
    }

    public function addCertInfo(string $key, string $salt): void
    {
        $this->load();
        $this->cert = $key;
        $this->salt = $salt;
        $this->save();
    }

    public function getAuthId(): int
    {
        return intval($this->id);
    }

    public function getAuthName(): string
    {
        return strval($this->login);
    }

    public function getGroup(): int
    {
        return intval($this->groupId);
    }

    public function getClass(): int
    {
        return IAccessClasses::CLASS_USER;
    }

    public function getStatus(): ?int
    {
        return static::USER_STATUS_ENABLED;
    }

    public function getDisplayName(): string
    {
        return strval($this->display);
    }

    public function getDir(): string
    {
        return '/';
    }

    public function getPubSalt(): string
    {
        return strval($this->salt);
    }

    public function getPubKey(): string
    {
        return strval($this->cert);
    }
}
