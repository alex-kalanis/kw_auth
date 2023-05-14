<?php

namespace kalanis\kw_auth\Sources\Mapper\Database;


use kalanis\kw_mapper\Mappers\Database\ADatabase;


/**
 * Class GroupsMapper
 * @package kalanis\kw_auth\Data\Mapper\Database
 * @codeCoverageIgnore remote source
 */
class GroupsMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('default_database');
        $this->setTable('groups');
        $this->setRelation('id', 'gr_id');
        $this->setRelation('name', 'gr_name');
        $this->setRelation('authorId', 'u_id');
        $this->setRelation('parents', 'gr_parents');
        $this->setRelation('desc', 'gr_desc');
        $this->setRelation('status', 'gr_status');
        $this->addPrimaryKey('id');
        $this->addForeignKey('authors', UsersRecord::class, 'authorId', 'id');
        $this->addForeignKey('members', UsersRecord::class, 'id', 'groupId');
    }
}
