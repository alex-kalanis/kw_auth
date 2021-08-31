<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\Data\Mapper;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IUser;


/**
 * Class Ldap
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via ldap
 * need kw_mapper!
 * @codeCoverageIgnore because access external content
 */
class Ldap implements IAuth
{
    /** @var Mapper\LdapRecord */
    protected $record = null;

    public function __construct()
    {
        $this->record = new Mapper\LdapRecord();
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        $mapper = $this->record->getMapper();
        if (!method_exists($mapper, 'authorize')) {
            return null;
        }
        /** @var Mapper\LdapMapper $mapper */
        return ($mapper->authorize([
            'user' => $userName,
            'password' => $params['password'] ?: ''
        ]))
            ? $this->getDataOnly($userName)
            : null ;
    }

    public function getDataOnly(string $userName): ?IUser
    {
        $record = new Mapper\LdapRecord();
        $record->name = $userName;
        $record->load();
        return (empty($record->getAuthId())) ? null : $record ;
    }
}
