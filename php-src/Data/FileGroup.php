<?php

namespace kalanis\kw_auth\Data;


use kalanis\kw_auth\Interfaces\IGroup;


/**
 * Class FileGroup
 * @package kalanis\kw_auth\Data
 */
class FileGroup implements IGroup
{
    protected $id = 0;
    protected $name = '';
    protected $author = 0;
    protected $displayName = '';

    public function setData(int $id, string $name, int $author, string $display): void
    {
        $this->id = $id;
        $this->name = $name;
        $this->author = $author;
        $this->displayName = $display;
    }

    public function getGroupId(): int
    {
        return $this->id;
    }

    public function getGroupName(): string
    {
        return $this->name;
    }

    public function getGroupAuthorId(): int
    {
        return $this->author;
    }

    public function getGroupDesc(): string
    {
        return $this->displayName;
    }
}
