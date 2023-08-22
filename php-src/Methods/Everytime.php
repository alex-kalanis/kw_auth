<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_auth_sources\Data\FileUser;
use kalanis\kw_auth_sources\Interfaces\IWorkClasses;


/**
 * Class Everytime
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate every time - for debugging purposes
 */
class Everytime extends AMethods
{
    public function process(\ArrayAccess $credentials): void
    {
        $this->loggedUser = new FileUser();
        $this->loggedUser->setUserData(
            '0',
            'Debug',
            '0',
            IWorkClasses::CLASS_USER,
            null,
            'Debug',
            '/'
        );
    }

    public function remove(): void
    {
    }
}
