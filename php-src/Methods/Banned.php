<?php

namespace kalanis\kw_auth\Methods;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_bans\Bans;
use kalanis\kw_bans\Sources\File;
use kalanis\kw_confs\Config;


/**
 * Class Banned
 * @package kalanis\kw_auth\AuthMethods
 * Throws an exception if incoming user is banned
 * Just add it before any other method which try to log user in
 * @codeCoverageIgnore because access external content
 */
class Banned extends AMethods
{
    const BAN_IP4 = 'ban_ip4.txt';
    const BAN_IP6 = 'ban_ip6.txt';
    const BAN_NAME = 'ban_name.txt';

    const PREG_IP4 = '#^[0-9\./]$#i';
    const PREG_IP6 = '#^[0-9a-f:/]$#i';
    const PREG_NAME = '#^[\*\?\:;\\//]$#i';

    protected $libBan = null;

    public function __construct(?IAuth $authenticator, ?AMethods $nextOne)
    {
        parent::__construct($authenticator, $nextOne);
        $banPath = $this->getBanPath();
        $this->libBan = new Bans(
            new File($banPath . DIRECTORY_SEPARATOR . self::BAN_IP4),
            new File($banPath . DIRECTORY_SEPARATOR . self::BAN_IP6),
            new File($banPath . DIRECTORY_SEPARATOR . self::BAN_NAME)
        );
    }

    protected function getBanPath(): string
    {
        $path = Config::getPath();
        return $path->getDocumentRoot() . DIRECTORY_SEPARATOR . 'conf';
    }

    public function process(\ArrayAccess $credentials): void
    {
        $name = $credentials->offsetExists('user') ? $credentials->offsetGet('user') : '' ;
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($this->libBan->has(
            preg_replace(static::PREG_IP4, '', $ip),
            preg_replace(static::PREG_IP6, '', $ip),
            preg_replace(static::PREG_NAME, '', $name)
        )) {
            throw new AuthException('Accessing user is banned!', 401);
        }
    }

    public function remove(): void
    {
    }
}
