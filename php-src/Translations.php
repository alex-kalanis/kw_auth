<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces\IKATranslations;


/**
 * Class Translations
 * @package kalanis\kw_auth
 */
class Translations implements IKATranslations
{
    public function kauPassFileNotFound(string $path): string
    {
        return 'File with passwords not found in preselect path';
    }

    public function kauPassFileNotSave(string $path): string
    {
        return 'File with passwords cannot be saved in preselect path';
    }

    public function kauPassMustBeSet(): string
    {
        return 'You must set the password to check!';
    }

    public function kauPassMissParam(): string
    {
        return 'Missing necessary params to determine the password';
    }

    public function kauPassLoginExists(): string
    {
        return 'Login name already exists!';
    }

    public function kauLockSystemNotSet(): string
    {
        return 'Lock system not set';
    }

    public function kauAuthAlreadyOpen(): string
    {
        return 'Someone works with authentication. Please try again a bit later.';
    }

    public function kauGroupMissParam(): string
    {
        return 'Missing necessary params to determine the group';
    }

    public function kauGroupHasMembers(): string
    {
        return 'Group to removal still has members. Remove them first.';
    }

    /**
     * @return string
     * @codeCoverageIgnore only on really specific installations
     */
    public function kauHashFunctionNotFound(): string
    {
        return 'Cannot find function for making hashes!';
    }

    /**
     * @return string
     * @codeCoverageIgnore processing bans
     */
    public function kauBanWantedUser(): string
    {
        return 'Accessing user is banned!';
    }

    /**
     * @return string
     * @codeCoverageIgnore processing sessions
     */
    public function kauTooManyTries(): string
    {
        return 'Too many tries!';
    }
}
