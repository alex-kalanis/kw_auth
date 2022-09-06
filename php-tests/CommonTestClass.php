<?php

use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IAuthCert;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_auth\Interfaces\IUserCert;
use kalanis\kw_locks\LockException;
use kalanis\kw_locks\Methods as LockMethod;
use kalanis\kw_locks\Interfaces as LockInt;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;
use PHPUnit\Framework\TestCase;


/**
 * Class CommonTestClass
 * The structure for mocking and configuration seems so complicated, but it's necessary to let it be totally idiot-proof
 */
class CommonTestClass extends TestCase
{
    /**
     * @throws LockException
     * @return LockMethod\FileLock
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
        $this->expectedUser->/** @scrutinizer ignore-call */addCertInfo(strval($certKey), strval($certSalt));
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


class MockModes implements IMode
{
    protected $knownPass = '';

    public function check(string $pass, string $hash): bool
    {
        return ($this->knownPass == $pass) || ('valid' == $pass);
    }

    public function hash(string $pass, ?string $method = null): string
    {
        $this->knownPass = $pass;
        return 'validPass-' . $pass;
    }
}


class XFailedStorage implements IStorage
{
    protected $canOpen = false;
    protected $content = '';

    public function __construct(bool $canOpen = false, string $content = '')
    {
        $this->canOpen = $canOpen;
        $this->content = $content;
    }

    public function canUse(): bool
    {
        return false;
    }

    public function write(string $sharedKey, $data, ?int $timeout = null): bool
    {
        throw new StorageException('Mock');
    }

    public function read(string $sharedKey)
    {
        if ($this->canOpen) {
            return $this->content;
        }
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function remove(string $sharedKey): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function exists(string $sharedKey): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function lookup(string $mask): Traversable
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function increment(string $key): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function decrement(string $key): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function removeMulti(array $keys): array
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }
}
