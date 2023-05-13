<?php

namespace kalanis\kw_auth\Traits;


use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Translations;


/**
 * Trait TLang
 * @package kalanis\kw_auth\Traits
 */
trait TLang
{
    /** @var IKauTranslations|null */
    protected $auLang = null;

    public function setAuLang(?IKauTranslations $auLang = null): void
    {
        $this->auLang = $auLang;
    }

    public function getAuLang(): IKauTranslations
    {
        if (empty($this->auLang)) {
            $this->auLang = new Translations();
        }
        return $this->auLang;
    }
}
