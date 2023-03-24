<?php

namespace kalanis\kw_auth\Data;


use kalanis\kw_auth\Interfaces\IUser;


/**
 * Class FileUser
 * @package kalanis\kw_auth\Data
 */
class FileUser implements IUser
{
    /** @var int */
    protected $authId = 0;
    /** @var string */
    protected $authName = '';
    /** @var int */
    protected $authGroup = 0;
    /** @var int */
    protected $authClass = 0;
    /** @var int|null */
    protected $authStatus = null;
    /** @var string */
    protected $displayName = '';
    /** @var string */
    protected $dir = '';

    public function setData(int $authId, string $authName, int $authGroup, int $authClass, ?int $authStatus, string $displayName, string $dir): void
    {
        $this->authId = $authId;
        $this->authName = $authName;
        $this->authGroup = $authGroup;
        $this->authClass = $authClass;
        $this->authStatus = $authStatus;
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

    public function getStatus(): ?int
    {
        return $this->authStatus;
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
