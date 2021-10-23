<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\Interfaces\IAccessClasses;


/**
 * Class Classes
 * @package kalanis\kw_auth\Sources
 * Authenticate via files - manage internal classes
 */
class Classes implements IAccessClasses
{
    use TClasses;
}
