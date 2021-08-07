<?php

namespace kalanis\kw_auth\Data;


use kalanis\kw_auth\Interfaces\IUser;


/**
 * Class FileUser
 * @package kalanis\kw_auth\Data
 */
class FileUser implements IUser
{
    protected $authId = 0;
    protected $authName = '';
    protected $authGroup = 0;
    protected $authClass = 0;
    protected $displayName = '';
    protected $dir = '';

    public function setData(int $authId, string $authName, int $authGroup, int $authClass, string $displayName, string $dir): void
    {
        $this->authId = $authId;
        $this->authName = $authName;
        $this->authGroup = $authGroup;
        $this->authClass = $authClass;
        $this->displayName = $displayName;
        $this->dir = $dir;
    }

    public function getAuthId(): int
    {
        return $this->authId;
    }

    public function getAuthName(): string
    {
        return $this->authName;
    }

    public function getGroup(): int
    {
        return $this->authGroup;
    }

    public function getClass(): int
    {
        return $this->authClass;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getDir(): string
    {
        return $this->dir;
    }
}
