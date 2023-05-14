<?php

namespace kalanis\kw_auth\Data;


use kalanis\kw_auth\Interfaces\IUser;


/**
 * Class FileUser
 * @package kalanis\kw_auth\Data
 */
class FileUser implements IUser
{
    /** @var string */
    protected $authId = '0';
    /** @var string */
    protected $authName = '';
    /** @var string */
    protected $authGroup = '0';
    /** @var int */
    protected $authClass = 0;
    /** @var int|null */
    protected $authStatus = null;
    /** @var string */
    protected $displayName = '';
    /** @var string */
    protected $dir = '';

    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir): void
    {
        $this->authId = $authId ?? $this->authId;
        $this->authName = $authName ?? $this->authName;
        $this->authGroup = $authGroup ?? $this->authGroup;
        $this->authClass = $authClass ?? $this->authClass;
        $this->authStatus = $authStatus;
        $this->displayName = $displayName ?? $this->displayName;
        $this->dir = $dir ?? $this->dir;
    }

    public function getAuthId(): string
    {
        return $this->authId;
    }

    public function getAuthName(): string
    {
        return $this->authName;
    }

    public function getGroup(): string
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
