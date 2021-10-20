<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_bans\Bans;
use kalanis\kw_bans\BanException;
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
    const INPUT_NAME = 'name';
    const SERVER_REMOTE = 'REMOTE_ADDR';

    const BAN_IP4 = 'ban_ip4.txt';
    const BAN_IP6 = 'ban_ip6.txt';
    const BAN_NAME = 'ban_name.txt';

    const PREG_IP4 = '#^[0-9\./]$#i';
    const PREG_IP6 = '#^[0-9a-f:/]$#i';
    const PREG_NAME = '#^[\*\?\:;\\//]$#i';

    /** @var Bans */
    protected $libBan = null;
    /** @var ArrayAccess */
    protected $server = null;

    /**
     * @param IAuth|null $authenticator
     * @param AMethods|null $nextOne
     * @param ArrayAccess $server
     * @throws BanException
     */
    public function __construct(?IAuth $authenticator, ?AMethods $nextOne, ArrayAccess $server)
    {
        parent::__construct($authenticator, $nextOne);
        $this->server = $server;
        $this->libBan = $this->getBans();
    }

    /**
     * @return Bans
     * @throws BanException
     */
    protected function getBans(): Bans
    {
        $banPath = $this->getBanPath();
        return new Bans(
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
        $name = $credentials->offsetExists(static::INPUT_NAME) ? strval($credentials->offsetGet(static::INPUT_NAME)) : '' ;
        $ip = $this->server->offsetGet(static::SERVER_REMOTE);
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
