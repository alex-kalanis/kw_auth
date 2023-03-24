<?php

namespace kalanis\kw_auth\Data;


use kalanis\kw_auth\Interfaces\IGroup;


/**
 * Class FileGroup
 * @package kalanis\kw_auth\Data
 */
class FileGroup implements IGroup
{
    /** @var int */
    protected $id = 0;
    /** @var string */
    protected $name = '';
    /** @var int */
    protected $author = 0;
    /** @var string */
    protected $displayName = '';
    /** @var int */
    protected $status = 0;

    public function setData(int $id, string $name, int $author, string $display, int $status): void
    {
        $this->id = $id;
        $this->name = $name;
        $this->author = $author;
        $this->displayName = $display;
        $this->status = $status;
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

    public function getGroupStatus(): int
    {
        return $this->status;
    }
}
