<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Interfaces\IAccessClasses;


/**
 * Class Everytime
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate every time - for debugging purposes
 * @codeCoverageIgnore because access external content
 */
class Everytime extends AMethods
{
    public function process(\ArrayAccess $credentials): void
    {
        $this->loggedUser = new FileUser();
        $this->loggedUser->setData(
            0,
            'Debug',
            0,
            IAccessClasses::CLASS_USER,
            'Debug',
            '/'
        );
    }

    public function remove(): void
    {
    }
}
