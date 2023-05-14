<?php

namespace kalanis\kw_auth\Sources\Mapper;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces;
use kalanis\kw_auth\Traits\TSeparated;
use kalanis\kw_mapper\Search\Search;


/**
 * Class Database
 * @package kalanis\kw_auth\Sources\Mapper
 * Authenticate via Database
 * need kw_mapper!
 * @codeCoverageIgnore because access external content
 */
class Database implements Interfaces\IAuth, Interfaces\IAuthCert, Interfaces\IAccessGroups
{
    use TSeparated;

    /** @var Interfaces\IMode */
    protected $passMode = null;
    /** @var Database\UsersRecord */
    protected $usersRecord = null;
    /** @var Database\GroupsRecord */
    protected $groupsRecord = null;

    public function __construct(Interfaces\IMode $mode)
    {
        $this->passMode = $mode;
        $this->usersRecord = new Database\UsersRecord();
        $this->groupsRecord = new Database\GroupsRecord();
    }

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        $record = $this->getByLogin($userName);
        if (empty($record)) {
            return null;
        }
        if (!$this->passMode->check($params['password'] ?: '', $record->pass)) {
            return null;
        }
        return $record;
    }

    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        return $this->getByLogin($userName);
    }

    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): void
    {
        $record = clone $this->usersRecord;
        $record->login = $userName;
        $record->load();
        $record->cert = strval($certKey);
        $record->salt = strval($certSalt);
        $record->save();
    }

    public function getCertData(string $userName): ?Interfaces\IUserCert
    {
        return $this->getByLogin($userName);
    }

    protected function getByLogin(string $login): ?Database\UsersRecord
    {
        $record = clone $this->usersRecord;
        $record->login = $login;
        if (empty($record->count())) {
            return null;
        }
        $record->load();
        return $record;
    }

    public function createAccount(Interfaces\IUser $user, string $password): void
    {
        $record = clone $this->usersRecord;
        $this->checkLogin($user->getAuthName());
        $record->login = $user->getAuthName();
        $record->groupId = $user->getGroup();
        $record->display = $user->getDisplayName();
        $record->save(true);
    }

    public function readAccounts(): array
    {
        $search = new Search(clone $this->usersRecord);
        return $search->getResults();
    }

    public function updateAccount(Interfaces\IUser $user): bool
    {
        $this->checkLogin($user->getAuthName(), $user->getAuthId());
        $record = clone $this->usersRecord;
        $record->id = $user->getAuthId();
        $record->load();
        $record->login = $user->getAuthName();
        $record->groupId = $user->getGroup();
        $record->display = $user->getDisplayName();
        return $record->save();
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        $record = clone $this->usersRecord;
        $record->login = $userName;
        $record->load();
        $record->pass = $this->passMode->hash($passWord);
        return $record->save();
    }

    public function deleteAccount(string $userName): bool
    {
        $record = clone $this->usersRecord;
        $record->login = $userName;
        return $record->delete();
    }

    public function createGroup(Interfaces\IGroup $group): void
    {
        $record = clone $this->groupsRecord;
        $record->name = $group->getGroupName();
        $record->desc = $group->getGroupDesc();
        $record->authorId = $group->getGroupAuthorId();
        $record->parents = $this->compactStr($group->getGroupParents());
        $record->status = $group->getGroupStatus();
        $record->save(true);
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        $record = clone $this->groupsRecord;
        $record->id = $groupId;
        if (empty($record->count())) {
            return null;
        }
        $record->load();
        return $record;
    }

    public function readGroup(): array
    {
        $search = new Search(clone $this->groupsRecord);
        return $search->getResults();
    }

    public function updateGroup(Interfaces\IGroup $group): bool
    {
        $record = clone $this->groupsRecord;
        $record->id = $group->getGroupId();
        $record->load();
        $record->name = $group->getGroupName();
        $record->desc = $group->getGroupDesc();
        $record->parents = $this->compactStr($group->getGroupParents());
        $record->status = $group->getGroupStatus();
        return $record->save();
    }

    public function deleteGroup(string $groupId): bool
    {
        $users = clone $this->usersRecord;
        $users->groupId = $groupId;
        if (0 >= $users->count()) {
            // not empty group
            return false;
        }
        $record = clone $this->groupsRecord;
        $record->id = $groupId;
        return $record->delete();
    }

    protected function checkLogin(string $login, string $id = '0'): void
    {
        $user = clone $this->usersRecord;
        $user->login = $login;
        $amount = $user->count();
        if (1 > $amount) {
            return;
        }
        if (1 < $amount) {
            throw new AuthException('Too many users with that login!');
        }
        $user->load();
        if ($id && ($user->id != $id)) {
            throw new AuthException('Login already used.');
        }
    }
}
