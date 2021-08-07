<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IAccessClasses
 * @package kalanis\kw_auth\Interfaces
 * Accessing system classes
 * They are set against system and tells what is allowed to run
 */
interface IAccessClasses
{
    const CLASS_MAINTAINER = 1;
    const CLASS_ADMIN = 2;
    const CLASS_USER = 3;

    /**
     * @return string[]
     */
    public function readClasses(): array;
}
