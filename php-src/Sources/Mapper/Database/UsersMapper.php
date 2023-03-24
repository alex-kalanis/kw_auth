<?php

namespace kalanis\kw_auth\Sources\Mapper\Database;


use kalanis\kw_mapper\Mappers\Database\ADatabase;


/**
 * Class UsersMapper
 * @package kalanis\kw_auth\Data\Mapper\Database
 * @codeCoverageIgnore remote source
 */
class UsersMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('default_database');
        $this->setTable('users');
        $this->setRelation('id', 'u_id');
        $this->setRelation('login', 'u_login');
        $this->setRelation('pass', 'u_pass');
        $this->setRelation('groupId', 'gr_id');
        $this->setRelation('display', 'u_display');
        $this->setRelation('cert', 'u_cert');
        $this->setRelation('salt', 'u_salt');
        $this->addPrimaryKey('id');
        $this->addForeignKey('groups', GroupsRecord::class, 'groupId', 'id');
    }
}
