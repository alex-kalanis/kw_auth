<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_accounts\Data\FileUser;
use kalanis\kw_accounts\Interfaces\IProcessClasses;


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
            IProcessClasses::CLASS_USER,
            null,
            'Debug',
            '/'
        );
    }

    public function remove(): void
    {
    }
}
