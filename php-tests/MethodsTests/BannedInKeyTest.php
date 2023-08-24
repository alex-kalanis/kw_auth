<?php

namespace MethodsTests;


use CommonTestClass;
use kalanis\kw_accounts\AccountsException;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Methods;
use kalanis\kw_bans\BanException;
use kalanis\kw_bans\Bans;
use kalanis\kw_bans\Interfaces\IKBTranslations;
use kalanis\kw_bans\Sources;
use kalanis\kw_bans\Sources\ASources;


class BannedInKeyTest extends CommonTestClass
{
    /**
     * @throws AccountsException
     * @throws AuthException
     */
    public function testServerPass(): void
    {
        $method = $this->getServerLib('name', ['foo', 'bar', 'baz']);
        $this->assertFalse($method->isAuthorized());
        $method->process(new \MockCredentials());
        $method->remove();
    }

    /**
     * @throws AccountsException
     * @throws AuthException
     */
    public function testServerCatch(): void
    {
        $method = $this->getServerLib('name', ['foo', 'testing']);
        $this->expectException(AuthException::class);
        $method->process(new \MockCredentials());
    }

    /**
     * @throws AccountsException
     * @throws AuthException
     */
    public function testServerDie(): void
    {
        $method = $this->getServerLib('name', ['foo', 'bar', 'baz'], new XFailBan(new Sources\Arrays([])));
        $this->expectException(AuthException::class);
        $method->process(new \MockCredentials());
    }

    /**
     * @throws AccountsException
     * @throws AuthException
     */
    public function testCredentialPass(): void
    {
        $method = $this->getCredentialLib('name', ['foo', 'bar', 'baz']);
        $this->assertFalse($method->isAuthorized());
        $method->process(new \MockCredentials(['name' => 'testing', 'pass' => 'dummy', ]));
        $method->remove();
    }

    /**
     * @throws AccountsException
     * @throws AuthException
     */
    public function testCredentialCatch(): void
    {
        $method = $this->getCredentialLib('name', ['foo', 'bar', 'baz']);
        $this->expectException(AuthException::class);
        $method->process(new \MockCredentials(['name' => 'bar', 'pass' => 'oof', ]));
    }

    /**
     * @throws AccountsException
     * @throws AuthException
     */
    public function testCredentialDie(): void
    {
        $method = $this->getCredentialLib('name', ['foo', 'bar', 'baz'], new XFailBan(new Sources\Arrays([])));
        $this->expectException(AuthException::class);
        $method->process(new \MockCredentials(['name' => 'testing', 'pass' => 'dummy', ]));
    }

    /**
     * @param string $whichKey
     * @param string[] $toBan
     * @param Bans\ABan|null $libBan
     * @return Methods\AMethods
     */
    protected function getServerLib(string $whichKey, array $toBan = [], ?Bans\ABan $libBan = null): Methods\AMethods
    {
        return new Methods\BannedInServerKey(
            new \MockAuth(),
            null,
            new \MockCredentials(
                ['name' => 'testing', 'pass' => 'dummy', ]
            ),
            $libBan ?: new Bans\Basic(new Sources\Arrays($toBan)),
            $whichKey
        );
    }

    /**
     * @param string $whichKey
     * @param string[] $toBan
     * @param Bans\ABan|null $libBan
     * @return Methods\AMethods
     */
    protected function getCredentialLib(string $whichKey, array $toBan = [], ?Bans\ABan $libBan = null): Methods\AMethods
    {
        return new Methods\BannedInCredentialKey(
            new \MockAuth(),
            null,
            $libBan ?: new Bans\Basic(new Sources\Arrays($toBan)),
            $whichKey
        );
    }
}


class XFailBan extends Bans\ABan
{
    public function __construct(ASources $source, ?IKBTranslations $lang = null)
    {
    }

    public function setLookedFor(string $lookedFor): void
    {
        throw new BanException('mock');
    }

    protected function matched(): array
    {
        return [];
    }
}
