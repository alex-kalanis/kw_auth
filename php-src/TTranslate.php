<?php

namespace kalanis\kw_auth;


use kalanis\kw_auth\Interfaces\IKATranslations;


/**
 * Trait TTranslate
 * @package kalanis\kw_auth
 */
trait TTranslate
{
    /** @var IKATranslations */
    protected $lang = null;

    public function setLang(?IKATranslations $lang): void
    {
        $this->lang = $lang ?: new Translations();
    }

    public function getLang(): IKATranslations
    {
        return $this->lang;
    }
}
