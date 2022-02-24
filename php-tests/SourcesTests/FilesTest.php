<?php

namespace SourcesTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Sources\Files;
use kalanis\kw_locks\LockException;


class FilesTest extends CommonTestClass
{
    protected $sourcePath = '';

    protected function setUp(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data';
    }

    /**
     * @throws AuthException
     * @throws LockException
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
     * @throws LockException
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
     * @throws LockException
     */
    public function testAuthenticateNoPass(): void
    {
        $lib = $this->fileSources();
        $this->expectException(AuthException::class);
        $lib->authenticate('manager', []);
    }

    /**
     * @throws AuthException
     * @throws LockException
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
            'WheĐn yoĐu dđo nođt knđow',
            $user->getDir()
        );
        $user->addCertInfo('==public key for accessing that content==', 'hidden salt');
        $lib->updateAccount($user);
        $lib->updateCertKeys($user->getAuthName(), $user->getPubKey(), $user->getPubSalt());

        // update name
        $user->setData(
            $user->getAuthId(),
            'changed name',
            $user->getGroup(),
            $user->getClass(),
            $user->getDisplayName(),
            $user->getDir()
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
     * @throws LockException
     * AuthId is not correct but auth name is
     */
    public function testAccountUpdateFail(): void
    {
        $lib = $this->fileSources();
        $user = new FileCertUser();
        $user->setData(600, 'worker', 0, 0, 'Die on set', 'so_here');

        $this->expectException(AuthException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AuthException
     * @throws LockException
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
     * @throws LockException
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
     * @return Files
     * @throws LockException
     */
    protected function fileSources(): Files
    {
        return new Files(
            new \MockModes(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function wantedUser(): FileCertUser
    {
        $user = new FileCertUser();
        $user->setData(1003, 'another', 0, 0, 'Testing another', 'why_here');
        return $user;
    }

    /**
     * @throws AuthException
     * @throws LockException
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
        $this->assertEquals(1001, $saved->getGroupAuthorId());

        // update
        $group->setData(
            $group->getGroupId(),
            $group->getGroupName(),
            1002,
            'WheĐn yoĐu dđo nođt knđow'
        );
        $lib->updateGroup($group);

        // check data - again with new values
        $saved = $lib->getGroupDataOnly($group->getGroupId());
        $this->assertEquals('When you do not know', $saved->getGroupDesc()); // overwrite this
        $this->assertEquals(1001, $saved->getGroupAuthorId()); // cannot overwrite this

        // remove
        $lib->deleteGroup($group->getGroupId());
        // check for existence
        $this->assertEmpty($lib->getGroupDataOnly($group->getGroupId()));
    }

    /**
     * @throws AuthException
     * @throws LockException
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
     * @throws LockException
     */
    public function testDeleteGroupFail(): void
    {
        $lib = $this->fileSources();
        $this->expectException(AuthException::class);
        $lib->deleteGroup(1);
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testAllGroups(): void
    {
        $lib = $this->fileSources();
        $data = $lib->readGroup();
        $this->assertEquals('Maintainers', $data[0]->getGroupDesc());
        $this->assertEquals(1000, $data[1]->getGroupAuthorId());
    }

    protected function wantedGroup($name = 'another'): FileGroup
    {
        $user = new FileGroup();
        $user->setData(3, $name, 1001, 'Testing group');
        return $user;
    }
}
