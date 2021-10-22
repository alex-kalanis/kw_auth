<?php

namespace kalanis\kw_auth\Data;


use kalanis\kw_auth\Interfaces\IUserCert;


/**
 * Class FileCertUser
 * @package kalanis\kw_auth\Data
 */
class FileCertUser extends FileUser implements IUserCert
{
    use TCerts;
}
