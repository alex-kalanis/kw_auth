<?php

namespace SourcesTests\Files\Files;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\Sources\Files\Files\Files;
use kalanis\kw_auth\Statuses\Always;
use kalanis\kw_files\Access;
use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_locks\LockException;
use kalanis\kw_paths\PathsException;
use kalanis\kw_storage\Interfaces as storages_interfaces;
use kalanis\kw_storage\Storage\Key;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target;
use kalanis\kw_storage\StorageException;


class FilesTest extends CommonTestClass
{
    protected $sourcePath = ['data'];

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testNotExistsData(): void
    {
        $lib = $this->emptyFileSources();
        $this->assertNull($lib->getDataOnly('does not exist'));
        $this->assertNull($lib->getCertData('does not exist'));
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
        $this->assertEmpty($lib->getCertData('does not exist'));
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
     * @throws StorageException
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
     * @throws StorageException
     */
    public function testUpdateAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        $this->expectException(AuthException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testUpdatePasswordOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();

        $this->expectException(AuthException::class);
        $lib->updatePassword('someone', 'not important');
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testUpdateCertsOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();

        $this->expectException(AuthException::class);
        $lib->updateCertKeys('someone', 'can be empty in this case', 'not important');
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testDeleteAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        $this->expectException(AuthException::class);
        $lib->deleteAccount($user->getAuthName());
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testDeleteAccountOnPartialInstance(): void
    {
        $lib = $this->partialFileSources();
        $user = $this->wantedUser();

        $this->expectException(AuthException::class);
        $lib->deleteAccount($user->getAuthName());
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
        $user->setUserData(
            null,
            null,
            null,
            2,
            3,
            'WheĐn yoĐu dđo nođt knđow',
            null
        );
        $user->addCertInfo('==public key for accessing that content==', 'hidden salt');
        $lib->updateAccount($user);
        $lib->updateCertKeys($user->getAuthName(), $user->getPubKey(), $user->getPubSalt());

        // update name
        $user->setUserData(
            null,
            'changed name',
            null,
            null,
            null,
            null,
            null
        );
        $lib->updateAccount($user);

        // check data - again with new values
        $saved = $lib->getCertData($user->getAuthName());
        $this->assertEquals('When you do not know', $saved->getDisplayName());
        $this->assertEquals(2, $saved->getClass());
        $this->assertEquals($user->getPubKey(), $saved->getPubKey());
        $this->assertEquals($user->getPubSalt(), $saved->getPubSalt());


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
     * AuthId is not correct but auth name is
     */
    public function testAccountUpdateFail(): void
    {
        $lib = $this->fileSources();
        $user = new FileCertUser();
        $user->setUserData('600', 'worker', '0', 0, 2, 'Die on set', 'so_here');

        $this->expectException(AuthException::class);
        $lib->updateAccount($user);
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
     * Contains a full comedy/tragedy of work with locks
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     * @return Files
     */
    protected function fileSources(): Files
    {
        Key\DirKey::setDir('');
        return new Files(
            (new Access\Factory())->getClass(new Storage(new Key\DirKey(), $this->filledMemoryAllFiles())),
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
    protected function filledMemoryAllFiles(): storages_interfaces\ITarget
    {
        $lib = new Target\Memory();
        $lib->save(DIRECTORY_SEPARATOR . 'data', IProcessNodes::STORAGE_NODE_KEY); // emulate root key
        $lib->save(DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . IFile::PASS_FILE, 'owner:1000:0:1:1:Owner:/data/:' . "\r\n"
            . 'manager:1001:1:2:1:Manage:/data/:' . "\r\n"
            . '# commented out' . "\r\n"
            . 'worker:1002:1:3:1:Worker:/data/:' . "\r\n"
            // last line is intentionally empty one
        );
        $lib->save(DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . IFile::SHADE_FILE, 'owner:M2FjMjZhMjc3MGY4MzUxYjYyN2YzMzI1NjRkNTVlYmM4N2U5N2Y3ODI2NDAwMjY0MTZmMTI0NTliOTFlMTUxZQ==:0:9999999999:7:x:' . "\r\n"
            . 'manager:ZWZmNzQwODIxZDhjNzRkMjZlZTIzYjQ2ODBiNDA1YTA5MWY0ZjdkNWVhNzk2NDAxZTZkODY3NDhmMjg0MzE4Yw==:0:9999999999:salt_hash:x:' . "\r\n"
            . '# commented out' . "\r\n"
            . 'worker:M2FjMjZhMjc3MGY4MzUxYjYyN2YzMzI1NjRkNTVlYmM4N2U5N2Y3ODI2NDAwMjY0MTZmMTI0NTliOTFlMTUxZQ==:0:9999999999:salt_key:x:' . "\r\n"
        // last line is intentionally empty one
        );
        $lib->save(DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . IFile::GROUP_FILE, '0:root:1000:Maintainers:1:' . "\r\n"
            . '1:admin:1000:Administrators:1:' . "\r\n"
            . '# commented out' . "\r\n"
            . '2:user:1000:All users:1:' . "\r\n"
        // last line is intentionally empty one
        );
        return $lib;
    }

    /**
     * Contains a partial files - no groups or shadow files
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     * @return Files
     */
    protected function partialFileSources(): Files
    {
        Key\DirKey::setDir('');
        return new Files(
            (new Access\Factory())->getClass(new Storage(new Key\DirKey(), $this->filledMemorySingleFile())),
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
        $lib->save(DIRECTORY_SEPARATOR . 'data', IProcessNodes::STORAGE_NODE_KEY); // emulate root key
        $lib->save(DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . IFile::PASS_FILE, 'owner:1000:0:1:1:Owner:/data/:' . "\r\n"
            . 'manager:1001:1:2:1:Manage:/data/:' . "\r\n"
            . '# commented out' . "\r\n"
            . 'worker:1002:1:3:1:Worker:/data/:' . "\r\n"
        // last line is intentionally empty one
        );
        return $lib;
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     * @return Files
     */
    protected function emptyFileSources(): Files
    {
        return new Files(
            (new Access\Factory())->getClass(new Storage(new Key\DefaultKey(), $this->filledMemoryEmulateDir())),
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
    protected function filledMemoryEmulateDir(): storages_interfaces\ITarget
    {
        $lib = new Target\Memory();
        $lib->save(DIRECTORY_SEPARATOR . 'data', IProcessNodes::STORAGE_NODE_KEY); // emulate root key
        return $lib;
    }

    protected function wantedUser(): FileCertUser
    {
        $user = new FileCertUser();
        $user->setUserData('1003', 'another', '0', 0, 1, 'Testing another', 'why_here');
        return $user;
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testGroupManipulation(): void
    {
        $lib = $this->fileSources();
        $group = $this->wantedGroup();

        // create
        $lib->createGroup($group);
        // check data
        $saved = $lib->getGroupDataOnly($group->getGroupId());
        $this->assertEquals('another', $saved->getGroupName());
        $this->assertEquals('Testing group', $saved->getGroupDesc());
        $this->assertEquals('1001', $saved->getGroupAuthorId());

        // update
        $group->setGroupData(
            $group->getGroupId(),
            $group->getGroupName(),
            'WheĐn yoĐu dđo nođt knđow',
            '1002',
            888
        );
        $lib->updateGroup($group);

        // check data - again with new values
        $saved = $lib->getGroupDataOnly($group->getGroupId());
        $this->assertEquals('When you do not know', $saved->getGroupDesc()); // overwrite this
        $this->assertEquals('1001', $saved->getGroupAuthorId()); // cannot overwrite this

        // remove
        $lib->deleteGroup($group->getGroupId());
        // check for existence
        $this->assertEmpty($lib->getGroupDataOnly($group->getGroupId()));
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testCreateGroupFail(): void
    {
        $lib = $this->fileSources();
        $group = $this->wantedGroup('');
        $this->expectException(AuthException::class);
        $lib->createGroup($group);
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testDeleteGroupFail(): void
    {
        $lib = $this->fileSources();
        $this->expectException(AuthException::class);
        $lib->deleteGroup('1');
    }

    /**
     * @throws AuthException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @throws StorageException
     */
    public function testAllGroups(): void
    {
        $lib = $this->fileSources();
        $data = $lib->readGroup();
        $this->assertEquals('Maintainers', $data[0]->getGroupDesc());
        $this->assertEquals('1000', $data[1]->getGroupAuthorId());
    }

    protected function wantedGroup($name = 'another'): FileGroup
    {
        $user = new FileGroup();
        $user->setGroupData('3', $name, 'Testing group', '1001', 999);
        return $user;
    }
}
