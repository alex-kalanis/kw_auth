<?php

namespace kalanis\kw_auth\Mode;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\TTranslate;


/**
 * Class KwOrig
 * @package kalanis\kw_auth\Mode
 * older kwcms style of password hashing
 */
class KwOrig implements IMode
{
    use TTranslate;

    protected $salt = '';

    public function __construct(string $salt, ?IKATranslations $lang = null)
    {
        $this->setLang($lang);
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
     * @return string
     * @throws AuthException
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
            . substr($input,0, (int)($ln/2))
            . substr($salt,$ln*2, $ln)
            . substr($input, (int)($ln/2))
            . substr($salt,$ln*3, $ln);
    }

    /**
     * @param string $word
     * @return string
     * @throws AuthException
     */
    private function makeHash(string $word): string
    {
        if (function_exists('mhash')) {
            return mhash(MHASH_SHA256, $word);
        }
        // @codeCoverageIgnoreStart
        if (function_exists('hash')) {
            return hash('sha256', $word);
        }
        throw new AuthException($this->getLang()->kauHashFunctionNotFound());
        // @codeCoverageIgnoreEnd
    }
}
