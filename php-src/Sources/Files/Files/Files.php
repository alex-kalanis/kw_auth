<?php

namespace kalanis\kw_auth\Sources\Files\Files;


use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Interfaces\IStatus;
use kalanis\kw_auth\Sources\Files\AFiles;
use kalanis\kw_files\Access\CompositeAdapter;
use kalanis\kw_locks\Interfaces\ILock;


/**
 * Class Files
 * @package kalanis\kw_auth\Sources\Files\Files
 * Authenticate via multiple files
 */
class Files extends AFiles
{
    use TFiles;

    public function __construct(CompositeAdapter $files, IMode $mode, IStatus $status, ILock $lock, string $dir, ?IKauTranslations $lang = null)
    {
        $this->files = $files;
        parent::__construct($mode, $status, $lock, $dir, $lang);
    }
}
