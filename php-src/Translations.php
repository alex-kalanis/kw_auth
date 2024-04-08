<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces\IKauTranslations;


/**
 * Class Translations
 * @package kalanis\kw_auth
 */
class Translations implements IKauTranslations
{
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

    public function kauNoAuthTreeSet(): string
    {
        return 'No auth tree set!';
    }
}
