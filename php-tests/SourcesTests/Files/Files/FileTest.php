<?php

namespace SourcesTests\Files\Files;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Sources\Files\Files\File;
use kalanis\kw_auth\Statuses\Always;
use kalanis\kw_files\Access;
use kalanis\kw_files\FilesException;
use kalanis\kw_locks\LockException;
use kalanis\kw_paths\PathsException;
use kalanis\kw_storage\Interfaces as storages_interfaces;
use kalanis\kw_storage\Storage\Key;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target;
use kalanis\kw_storage\StorageException;


class FileTest extends AFilesTests
{
    protected $sourcePath = '.passcomb';

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testNotExistsData(): void
    {
        $lib = $this->emptyFileSources();
        $this->assertNull($lib->getDataOnly('does not exist'));
        $this->assertNull($lib->authenticate('does not exist', ['password' => 'not need']));
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testDataOnly(): void
    {
        $lib = $this->fileSources();
        $this->assertEmpty($lib->getDataOnly('does not exist'));
        $user = $lib->getDataOnly('manager');
        $this->assertNotEmpty($user);
        $this->assertEquals('Manage', $user->getDisplayName());
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testAuthenticate(): void
    {
        $lib = $this->fileSources();
        $this->assertEmpty($lib->authenticate('manager', ['password' => 'thisisnotreal']));
        $user = $lib->authenticate('manager', ['password' => 'valid']);
        $this->assertNotEmpty($user);
        $this->assertEquals('Manage', $user->getDisplayName());
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testAuthenticateNoPass(): void
    {
        $lib = $this->fileSources();
        $this->expectException(AuthException::class);
        $lib->authenticate('manager', []);
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testCreateAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        // create
        $lib->createAccount($user, 'here to set');

        // check data
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('Testing another', $saved->getDisplayName());
        $this->assertEquals('why_here', $saved->getDir());
        $this->assertEquals(3, $saved->getClass());
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testUpdateAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        // update
        $this->expectException(AuthException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testUpdatePasswordOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        // update
        $this->expectException(AuthException::class);
        $lib->updatePassword('Some user', 'not important');
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testRemoveAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        // delete
        $lib->deleteAccount($user->getAuthName());
        $this->assertNull($lib->getDataOnly($user->getAuthName()));
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testAccountManipulation(): void
    {
        $lib = $this->fileSources();
        $user = $this->wantedUser();

        // create
        $lib->createAccount($user, 'here to set');
        // check data
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('Testing another', $saved->getDisplayName());
        $this->assertEquals('why_here', $saved->getDir());
        $this->assertEquals(3, $saved->getClass());

        // check login
        $this->assertNotEmpty($lib->authenticate($user->getAuthName(), ['password' => 'here to set']));

        // update
        $user->setData(
            $user->getAuthId(),
            $user->getAuthName(),
            $user->getGroup(),
            2,
            3,
            'WheĐn yoĐu dđo nođt knđow',
            $user->getDir()
        );
        $lib->updateAccount($user);

        // check data - again with new values
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('When you do not know', $saved->getDisplayName());
        $this->assertEquals(2, $saved->getClass());

        // update password
        $lib->updatePassword($user->getAuthName(), 'another pass');
        // check login
        $this->assertEmpty($lib->authenticate($user->getAuthName(), ['password' => 'here to set']));
        $this->assertNotEmpty($lib->authenticate($user->getAuthName(), ['password' => 'another pass']));

        // remove
        $lib->deleteAccount($user->getAuthName());
        // check for existence
        $this->assertEmpty($lib->getDataOnly($user->getAuthName()));
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testCreateFail(): void
    {
        $lib = $this->fileSources();
        $user = $this->wantedUser();
        $this->expectException(AuthException::class);
        $lib->createAccount($user, '');
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testAllUsers(): void
    {
        $lib = $this->fileSources();
        $data = $lib->readAccounts();
        $this->assertEquals(1, $data[0]->getClass());
        $this->assertEquals('manager', $data[1]->getAuthName());
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testCreateAccountStorageFail(): void
    {
        $lib = $this->failedFileSources();
        $group = $this->wantedUser();
        $this->expectException(AuthException::class);
        $lib->createAccount($group, 'somewhere');
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testCreateAccountStorageFailSave(): void
    {
        $lib = $this->failedFileSources(true);
        $group = $this->wantedUser();
        $this->expectException(AuthException::class);
        $lib->createAccount($group, 'somewhere');
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testReadAccountsStorageFail(): void
    {
        $lib = $this->failedFileSources();
        $this->expectException(AuthException::class);
        $lib->readAccounts();
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testUpdateAccountStorageFail(): void
    {
        $lib = $this->failedFileSources();
        $this->expectException(AuthException::class);
        $lib->updateAccount($this->wantedUser());
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testUpdateAccountStorageFailSave(): void
    {
        $lib = $this->failedFileSources(true);
        $this->expectException(AuthException::class);
        $lib->updateAccount($this->wantedUser());
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testRemoveUserStorageFail(): void
    {
        $lib = $this->failedFileSources();
        $this->assertTrue($lib->deleteAccount('no-one'));
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     */
    public function testRemoveUserStorageFailSave(): void
    {
        $lib = $this->failedFileSources(true, '1000:owner:some-wanted:0:1:1:Owner:/data/:' . "\r\n" . '1002:worker:some-else:1:3:1:Worker:/data/:' . "\r\n");
        $this->expectException(AuthException::class);
        $lib->deleteAccount('worker');
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @return File
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    protected function fileSources(): File
    {
        return new File(
            (new Access\Factory())->getClass(new Storage(new Key\DefaultKey(), $this->filledMemorySingleFile())),
            new \MockModes(),
            new Always(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * @throws StorageException
     * @return storages_interfaces\ITarget
     */
    protected function filledMemorySingleFile(): storages_interfaces\ITarget
    {
        $lib = new Target\Memory();
        $lib->save(DIRECTORY_SEPARATOR . $this->sourcePath, '1000:owner:$2y$10$6-bucFamnK5BTGbojaWw3!HzzHOlUNnN6PF3Y9qHQIdE8FmQKv/eq:0:1:1:Owner:/data/:' . "\r\n"
            . '1001:manager:$2y$10$G1Fo0udxqekABHkzUQubfuD8AjgD/5O9F9v3E0qYG2TI0BfZAkyz2:1:2:1:Manage:/data/:' . "\r\n"
            . '# commented out' . "\r\n"
            . '1002:worker:$2y$10$6.bucFamnK5BTGbojaWw3.HpzHOlQUnN6PF3Y9qHQIdE8FmQKv/eq:1:3:1:Worker:/data/:' . "\r\n"
        // last line is intentionally empty one
        );
        return $lib;
    }

    /**
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @return File
     */
    protected function emptyFileSources(): File
    {
        return new File(
            (new Access\Factory())->getClass(new Storage(new Key\DefaultKey(), new Target\Memory())),
            new \MockModes(),
            new Always(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * @param bool $canOpen
     * @param string $content
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @return File
     */
    protected function failedFileSources(bool $canOpen = false, string $content = ''): File
    {
        return new File(
            (new Access\Factory())->getClass(new \XFailedStorage($canOpen, $content)),
            new \MockModes(),
            new Always(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function wantedUser(): FileUser
    {
        $user = new FileUser();
        $user->setData(600, 'another', 0, 0, 2,'Testing another', 'why_here');
        return $user;
    }
}
