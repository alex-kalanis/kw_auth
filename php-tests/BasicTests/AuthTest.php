<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Interfaces;
use kalanis\kw_address_handler\Handler;
use kalanis\kw_address_handler\Sources as HandlerSources;
use kalanis\kw_auth\Auth;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\AuthTree;
use kalanis\kw_auth\Methods;
use kalanis\kw_auth_sources\Access;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Hashes\KwOrig;
use kalanis\kw_auth_sources\Statuses\Always;
use kalanis\kw_locks\LockException;


class AuthTest extends CommonTestClass
{
    public function testStatical(): void
    {
        $this->assertEmpty(Auth::getTree());
        Auth::fill(
            new Methods\Everytime(null, null)
        );
        $this->assertNotEmpty(Auth::getTree());

        $this->assertEmpty(Auth::getAuthenticator());
        $this->assertEmpty(Auth::getAuth());
        $this->assertEmpty(Auth::getAccounts());
        $this->assertEmpty(Auth::getClasses());
        $this->assertEmpty(Auth::getGroups());
        Auth::setAuthenticator(new XAUser());
        Auth::setAuth(new XAAuth());
        Auth::setAccounts(new XAAccounts());
        Auth::setClasses(new XAClasses());
        Auth::setGroups(new XAGroups());
        $this->assertNotEmpty(Auth::getAuthenticator());
        $this->assertNotEmpty(Auth::getAuth());
        $this->assertNotEmpty(Auth::getAccounts());
        $this->assertNotEmpty(Auth::getClasses());
        $this->assertNotEmpty(Auth::getGroups());
    }

    /**
     * @throws AccountsException
     * @throws AuthException
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testTree(): void
    {
        $tree = new AuthTree();
        $this->assertEmpty($tree->getMethod());

        // this is what should be in bootstrap
        $tree->setTree(
            new Methods\HttpDigest(
                $this->fileSources(),
                new Methods\Everytime(null, null),
                new \MockCredentials([
                    Methods\HttpDigest::INPUT_METHOD => 'PUT',
                    Methods\HttpDigest::INPUT_DIGEST => '0123456789qwertzuiopasdfghjklyxcvbnm--',
                ])
            )
        );

        // now run that
        $this->assertEmpty($tree->getMethod());
        $tree->findMethod(new \MockCredentials());
        $this->assertNotEmpty($tree->getMethod());
        $this->assertTrue($tree->getMethod()->isAuthorized());
        $this->assertEquals('Debug', $tree->getMethod()->getLoggedUser()->getAuthName());

        // tree with data from url
        $tree->setTree(new Methods\UrlCerts(
            $this->fileSources(),
            null,
            new Handler(new HandlerSources\Address('//abcdef/ghi/jkl'))
        ));
        $this->assertEmpty($tree->getMethod());
        $tree->findMethod(new \MockCredentials());
        $this->assertEmpty($tree->getMethod());
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws AuthSourcesException
     * @throws LockException
     * @return Interfaces\IAuthCert
     */
    protected function fileSources(): Interfaces\IAuthCert
    {
        return (new Access\Factory())->getSources([
            'storage' => implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data']), // todo: tohle je prefix cesty, takze na storage se cpe do prefixu; uprava v nizsich levelech
            'hash' => new KwOrig('yxcvbnmasdfghjklqwertzuiop0123456789'),
            'status' => new Always(),
            'lock' => $this->getLockPath(),
        ]);
    }
}


class XAUser implements Interfaces\IUser
{

    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir, ?array $extra = []): void
    {
    }

    public function getAuthId(): string
    {
        return '0';
    }

    public function getAuthName(): string
    {
        return '';
    }

    public function getGroup(): string
    {
        return '0';
    }

    public function getClass(): int
    {
        return 0;
    }

    public function getStatus(): int
    {
        return static::USER_STATUS_UNKNOWN;
    }

    public function getDisplayName(): string
    {
        return '';
    }

    public function getDir(): string
    {
        return '';
    }

    public function getExtra(): array
    {
        return [];
    }
}


class XAAuth implements Interfaces\IAuth
{
    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        return null;
    }

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        return null;
    }
}


class XAAccounts implements Interfaces\IProcessAccounts
{
    public function createAccount(Interfaces\IUser $user, string $password): bool
    {
        return true;
    }

    public function readAccounts(): array
    {
        return [];
    }

    public function updateAccount(Interfaces\IUser $user): bool
    {
        return true;
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        return true;
    }

    public function deleteAccount(string $userName): bool
    {
        return true;
    }
}


class XAGroups implements Interfaces\IProcessGroups
{

    public function createGroup(Interfaces\IGroup $group): bool
    {
        return true;
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        return null;
    }

    public function readGroup(): array
    {
        return [];
    }

    public function updateGroup(Interfaces\IGroup $group): bool
    {
        return true;
    }

    public function deleteGroup(string $groupId): bool
    {
        return true;
    }
}


class XAClasses implements Interfaces\IProcessClasses
{
    public function readClasses(): array
    {
        return [];
    }
}
