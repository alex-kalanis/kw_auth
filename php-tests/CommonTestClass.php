<?php

use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IAuthCert;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_auth\Interfaces\IUserCert;
use kalanis\kw_locks\LockException;
use kalanis\kw_locks\Methods as LockMethod;
use kalanis\kw_locks\Interfaces as LockInt;
use PHPUnit\Framework\TestCase;


/**
 * Class CommonTestClass
 * The structure for mocking and configuration seems so complicated, but it's necessary to let it be totally idiot-proof
 */
class CommonTestClass extends TestCase
{
    /**
     * @return LockMethod\FileLock
     * @throws LockException
     */
    protected function getLockPath(): LockMethod\FileLock
    {
        return new LockMethod\FileLock(
            __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . LockInt\ILock::LOCK_FILE
        );
    }
}


class MockAuth implements IAuth
{
    protected $expectedUser = null;
    protected $expectedPass = '';

    public function __construct(IUser $expectedUser = null, string $expectedPass = '')
    {
        $this->expectedUser = $expectedUser;
        $this->expectedPass = $expectedPass;
    }

    public function getDataOnly(string $userName): ?IUser
    {
        return $this->expectedUser;
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        return (
            $this->expectedUser
            && ($this->expectedUser->getAuthName() == $userName)
            && isset($params['password'])
            && ($params['password'] == $this->expectedPass)
        ) ? $this->expectedUser : null;
    }
}


class MockAuthCert extends MockAuth implements IAuthCert
{
    /** @var IUserCert|null */
    protected $expectedUser = null;

    public function __construct(IUserCert $expectedUser = null, string $expectedPass = '')
    {
        parent::__construct($expectedUser, $expectedPass);
    }

    public function createAccount(IUser $user, string $password): void
    {
    }

    public function readAccounts(): array
    {
        return [];
    }

    public function updateAccount(IUser $user): void
    {
    }

    public function updatePassword(string $userName, string $passWord): void
    {
    }

    public function deleteAccount(string $userName): void
    {
    }

    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): void
    {
        $this->expectedUser->addCertInfo(strval($certKey), strval($certSalt));
    }

    public function getCertData(string $userName): ?IUserCert
    {
        return empty($userName) ? null : $this->expectedUser;
    }
}


class MockCredentials extends ArrayObject
{
}


class MockUser implements IUser
{
    public function setData(int $authId, string $authName, int $authGroup, int $authClass, string $displayName, string $dir): void
    {
    }

    public function getAuthId(): int
    {
        return 654;
    }

    public function getAuthName(): string
    {
        return 'fool';
    }

    public function getGroup(): int
    {
        return 456789;
    }

    public function getClass(): int
    {
        return 999;
    }

    public function getDisplayName(): string
    {
        return 'FooL';
    }

    public function getDir(): string
    {
        return 'not_available\\:///';
    }
}


class MockUserToFill extends FileUser
{
    public function __construct(int $authId, string $authName, int $authGroup, int $authClass, string $displayName, string $dir)
    {
        $this->setData($authId, $authName, $authGroup, $authClass, $displayName, $dir);
    }
}
