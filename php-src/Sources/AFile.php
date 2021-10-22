<?php

namespace kalanis\kw_auth\Sources;


/**
 * Class AFile
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via files - abstract
 */
abstract class AFile
{
    use TFiles;
    use TLines;

    protected $path = '';
}
