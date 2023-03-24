<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\Interfaces\IUser;


/**
 * Trait TStatusTransform
 * @package kalanis\kw_auth\Sources
 * Status - integer to string and back
 */
trait TStatusTransform
{
    protected function transformFromIntToString(?int $value): string
    {
        return is_null($value) ? IUser::STATUS_NONE : strval($value);
    }

    protected function transformFromStringToInt(string $value): ?int
    {
        return IUser::STATUS_NONE == $value ? null : intval($value);
    }
}
