<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IKATranslations
 * @package kalanis\kw_auth\Interfaces
 * Translations
 */
interface IKATranslations
{
    public function kauPassFileNotFound(string $path): string;

    public function kauPassFileNotSave(string $path): string;

    public function kauPassMustBeSet(): string;

    public function kauPassMissParam(): string;

    public function kauPassLoginExists(): string;

    public function kauLockSystemNotSet(): string;

    public function kauAuthAlreadyOpen(): string;

    public function kauGroupMissParam(): string;

    public function kauGroupHasMembers(): string;

    public function kauHashFunctionNotFound(): string;

    public function kauBanWantedUser(): string;

    public function kauTooManyTries(): string;
}
