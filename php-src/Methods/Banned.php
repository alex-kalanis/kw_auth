<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Traits\TLang;
use kalanis\kw_bans\Bans;
use kalanis\kw_bans\BanException;
use kalanis\kw_bans\Interfaces\IKBTranslations;
use kalanis\kw_bans\Sources\File;
use kalanis\kw_paths\Path;


/**
 * Class Banned
 * @package kalanis\kw_auth\AuthMethods
 * Throws an exception if incoming user is banned
 * Just add it before any other method which try to log user in
 * @codeCoverageIgnore because access external content
 */
class Banned extends AMethods
{
    use TLang;

    const INPUT_NAME = 'name';
    const SERVER_REMOTE = 'REMOTE_ADDR';

    const BAN_IP4 = 'ban_ip4.txt';
    const BAN_IP6 = 'ban_ip6.txt';
    const BAN_NAME = 'ban_name.txt';

    const PREG_IP4 = '#^[0-9\./]$#i';
    const PREG_IP6 = '#^[0-9a-f:/]$#i';
    const PREG_NAME = '#^[\*\?\:;\\//]$#i';

    /** @var Path */
    protected $libPath = null;
    /** @var Bans */
    protected $libBan = null;
    /** @var ArrayAccess<string, string|int|float> */
    protected $server = null;

    /**
     * @param IAuth|null $authenticator
     * @param AMethods|null $nextOne
     * @param Path $path
     * @param ArrayAccess<string, string|int|float> $server
     * @param IKauTranslations|null $kauLang
     * @param IKBTranslations|null $kbLang
     * @throws BanException
     */
    public function __construct(
        ?IAuth $authenticator,
        ?AMethods $nextOne,
        Path $path,
        ArrayAccess $server,
        ?IKauTranslations $kauLang = null,
        ?IKBTranslations $kbLang = null
    ) {
        parent::__construct($authenticator, $nextOne);
        $this->setAuLang($kauLang);
        $this->libPath = $path;
        $this->server = $server;
        $this->libBan = $this->getBans($kbLang);
    }

    /**
     * @param IKBTranslations|null $kbLang
     * @throws BanException
     * @return Bans
     */
    protected function getBans(?IKBTranslations $kbLang): Bans
    {
        $banPath = $this->getBanPath();
        return new Bans(
            $kbLang,
            new File($banPath . DIRECTORY_SEPARATOR . self::BAN_IP4),
            new File($banPath . DIRECTORY_SEPARATOR . self::BAN_IP6),
            new File($banPath . DIRECTORY_SEPARATOR . self::BAN_NAME)
        );
    }

    protected function getBanPath(): string
    {
        return $this->libPath->getDocumentRoot() . $this->libPath->getPathToSystemRoot() . DIRECTORY_SEPARATOR . 'conf';
    }

    public function process(\ArrayAccess $credentials): void
    {
        $name = $credentials->offsetExists(static::INPUT_NAME) ? strval($credentials->offsetGet(static::INPUT_NAME)) : '' ;
        $ip = strval($this->server->offsetGet(static::SERVER_REMOTE));
        try {
            if ($this->libBan->has(
                strval(preg_replace(static::PREG_IP4, '', $ip)),
                strval(preg_replace(static::PREG_IP6, '', $ip)),
                strval(preg_replace(static::PREG_NAME, '', $name))
            )) {
                throw new AuthException($this->getAuLang()->kauBanWantedUser(), 401);
            }
        } catch (BanException $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function remove(): void
    {
    }
}
