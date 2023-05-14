<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_address_handler\Handler;
use kalanis\kw_address_handler\Sources as HandlerSources;
use kalanis\kw_auth\Auth;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\AuthTree;
use kalanis\kw_auth\Interfaces;
use kalanis\kw_auth\Methods;
use kalanis\kw_auth\Mode\KwOrig;
use kalanis\kw_auth\Sources;
use kalanis\kw_auth\Statuses\Always;
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
     * @throws AuthException
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
     * @throws LockException
     * @return Sources\Files\Volume\Files
     */
    protected function fileSources(): Sources\Files\Volume\Files
    {
        return new Sources\Files\Volume\Files(
            new KwOrig('yxcvbnmasdfghjklqwertzuiop0123456789'),
            new Always(),
            $this->getLockPath(),
            [__DIR__, '..', 'data']
        );
    }
}


class XAUser implements Interfaces\IUser
{

    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir): void
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

    public function getStatus(): ?int
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


class XAAccounts implements Interfaces\IAccessAccounts
{
    public function createAccount(Interfaces\IUser $user, string $password): void
    {
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


class XAGroups implements Interfaces\IAccessGroups
{

    public function createGroup(Interfaces\IGroup $group): void
    {
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


class XAClasses implements Interfaces\IAccessClasses
{
    public function readClasses(): array
    {
        return [];
    }
}
