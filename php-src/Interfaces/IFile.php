<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IFile
 * @package kalanis\kw_auth\Interfaces
 * Authentication file support
 */
interface IFile
{
    const SEPARATOR = ':'; # separate params with...
    const PASS_FILE = '.passwd'; # password file
    const SHADE_FILE = '.shadow'; # shadow file
    const GROUP_FILE = '.groups'; # group file
    const CRLF = "\r\n"; # line ending
    const PARENT_SEPARATOR = ','; # separate multiple values in one entry (like groups)
}
