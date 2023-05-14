<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IUser
 * @package kalanis\kw_auth\Interfaces
 * User data from your auth system
 */
interface IUser
{
    const LOWEST_USER_ID = '1000';
    const STATUS_NONE = 'none';

    const USER_STATUS_UNKNOWN = null;
    const USER_STATUS_DISABLED = 0;
    const USER_STATUS_ENABLED = 1;
    const USER_STATUS_ONLY_LOGIN = 2;
    const USER_STATUS_ONLY_CERT = 3;

    /**
     * Fill user; null values will not change
     * @param string|null $authId
     * @param string|null $authName
     * @param string|null $authGroup
     * @param int|null $authClass
     * @param int|null $authStatus
     * @param string|null $displayName
     * @param string|null $dir
     */
    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir): void;

    public function getAuthId(): string;

    public function getAuthName(): string;

    public function getGroup(): string;

    public function getClass(): int;

    public function getStatus(): ?int;

    public function getDisplayName(): string;

    public function getDir(): string;
}
