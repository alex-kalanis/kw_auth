<?php

namespace kalanis\kw_auth\Interfaces;


/**
 * Interface IGroup
 * @package kalanis\kw_auth\Interfaces
 * Group data from your auth system
 */
interface IGroup
{
    public function getGroupId(): int;

    public function getGroupName(): string;

    public function getGroupDesc(): string;

    public function getGroupAuthorId(): int;
}
