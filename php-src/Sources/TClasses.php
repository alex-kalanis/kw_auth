<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\Interfaces\IAccessClasses;


/**
 * Trait TClasses
 * @package kalanis\kw_auth\Sources
 * Authenticate via files - manage internal classes
 */
trait TClasses
{
    /**
     * @return string[]
     */
    public function readClasses(): array
    {
        return [
            IAccessClasses::CLASS_MAINTAINER => 'Maintainer',
            IAccessClasses::CLASS_ADMIN => 'Admin',
            IAccessClasses::CLASS_USER => 'User',
        ];
    }
}
