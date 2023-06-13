<?php

namespace kalanis\kw_auth\Mode;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Traits\TLang;


/**
 * Class KwOrig
 * @package kalanis\kw_auth\Mode
 * older kwcms style of password hashing
 */
class KwOrig implements IMode
{
    use TLang;

    /** @var string */
    protected $salt = '';

    public function __construct(string $salt, ?IKauTranslations $lang = null)
    {
        $this->setAuLang($lang);
        $this->salt = $salt;
    }

    public function check(string $pass, string $hash): bool
    {
        return $hash == $this->hashPassword($pass);
    }

    public function hash(string $pass, ?string $method = null): string
    {
        return $this->hashPassword($pass);
    }

    /**
     * @param string $input
     * @throws AuthException
     * @return string
     */
    protected function hashPassword(string $input): string
    {
        return base64_encode(bin2hex($this->makeHash($this->passSalt($input))));
    }

    private function passSalt(string $input): string
    {
        $ln = strlen($input);
        # pass is too long and salt too short
        $salt = (strlen($this->salt) < ($ln*5))
            ? str_repeat($this->salt, 5)
            : $this->salt ;
        return substr($salt, $ln, $ln)
            . substr($input,0, intval($ln/2))
            . substr($salt,$ln*2, $ln)
            . substr($input, intval($ln/2))
            . substr($salt,$ln*3, $ln);
    }

    /**
     * @param string $word
     * @throws AuthException
     * @return string
     */
    private function makeHash(string $word): string
    {
        if (function_exists('hash')) {
            return strval(hash('sha256', $word));
        }
        // @codeCoverageIgnoreStart
        throw new AuthException($this->getAuLang()->kauHashFunctionNotFound());
        // @codeCoverageIgnoreEnd
    }
}
