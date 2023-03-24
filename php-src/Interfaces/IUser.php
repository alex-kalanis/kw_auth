<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IUser
 * @package kalanis\kw_auth\Interfaces
 * User data from your auth system
 */
interface IUser
{
    const LOWEST_USER_ID = 1000;
    const STATUS_NONE = 'none';

    const USER_STATUS_UNKNOWN = null;
    const USER_STATUS_DISABLED = 0;
    const USER_STATUS_ENABLED = 1;
    const USER_STATUS_ONLY_LOGIN = 2;
    const USER_STATUS_ONLY_CERT = 3;

    public function setData(int $authId, string $authName, int $authGroup, int $authClass, ?int $authStatus, string $displayName, string $dir): void;

    public function getAuthId(): int;

    public function getAuthName(): string;

    public function getGroup(): int;

    public function getClass(): int;

    public function getStatus(): ?int;

    public function getDisplayName(): string;

    public function getDir(): string;
}
