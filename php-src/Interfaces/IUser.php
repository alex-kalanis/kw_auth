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

    public function setData(int $authId, string $authName, int $authGroup, int $authClass, string $displayName, string $dir): void;

    public function getAuthId(): int;

    public function getAuthName(): string;

    public function getGroup(): int;

    public function getClass(): int;

    public function getDisplayName(): string;

    public function getDir(): string;
}
