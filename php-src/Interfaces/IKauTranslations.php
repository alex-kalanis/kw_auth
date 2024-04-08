<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IKauTranslations
 * @package kalanis\kw_auth\Interfaces
 * Translations
 */
interface IKauTranslations
{
    public function kauBanWantedUser(): string;

    public function kauTooManyTries(): string;

    public function kauNoAuthTreeSet(): string;
}
