<?php

namespace kalanis\kw_auth\Methods;


use ArrayAccess;
use kalanis\kw_accounts\Interfaces\IAuth;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Traits\TLang;
use kalanis\kw_bans\Bans;
use kalanis\kw_bans\BanException;


/**
 * Class BannedInServerKey
 * @package kalanis\kw_auth\AuthMethods
 * Throws an exception if incoming user is banned
 * Just add it before any other method which try to log user in
 */
class BannedInServerKey extends AMethods
{
    use TLang;

    protected Bans\ABan $libBan;
    /** @var ArrayAccess<string, string|int|float> */
    protected ArrayAccess $server;
    protected string $credentialKey = '';

    /**
     * @param IAuth|null $authenticator
     * @param AMethods|null $nextOne
     * @param ArrayAccess<string, string|int|float> $server
     * @param Bans\ABan $ban
     * @param string $credentialKey
     * @param IKauTranslations|null $kauLang
     */
    public function __construct(
        ?IAuth $authenticator,
        ?AMethods $nextOne,
        ArrayAccess $server,
        Bans\ABan $ban,
        string $credentialKey,
        ?IKauTranslations $kauLang = null
    ) {
        parent::__construct($authenticator, $nextOne);
        $this->setAuLang($kauLang);
        $this->server = $server;
        $this->libBan = $ban;
        $this->credentialKey = $credentialKey;
    }

    public function process(\ArrayAccess $credentials): void
    {
        try {
            if ($this->server->offsetExists($this->credentialKey)) {
                $this->libBan->setLookedFor(strval($this->server->offsetGet($this->credentialKey)));
                if ($this->libBan->isBanned()) {
                    throw new AuthException($this->getAuLang()->kauBanWantedUser(), 401);
                }
            }
        } catch (BanException $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function remove(): void
    {
    }
}
