<?php

namespace SourcesTests\Files\Volume;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Sources\Files\Volume\Files;
use kalanis\kw_auth\Statuses\Always;
use kalanis\kw_locks\LockException;


class FilesTest extends CommonTestClass
{
    protected $sourcePath = [];

    protected function setUp(): void
    {
        $this->sourcePath = array_merge(explode(DIRECTORY_SEPARATOR, __DIR__), ['..', '..', '..', 'data']);
    }

    /**
     * @throws AuthException
     * @throws LockException
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
        $user->setUserData(
            null,
            null,
            null,
            2,
            7,
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
     * @throws LockException
     * AuthId is not correct but auth name is
     */
    public function testAccountUpdateFail(): void
    {
        $lib = $this->fileSources();
        $user = new FileCertUser();
        $user->setUserData('600', 'worker', '0', 0, null, 'Die on set', 'so_here');

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
     * @throws LockException
     * @return Files
     */
    protected function fileSources(): Files
    {
        return new Files(
            new \MockModes(),
            new Always(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function wantedUser(): FileCertUser
    {
        $user = new FileCertUser();
        $user->setUserData('1003', 'another', '0', 0, 12, 'Testing another', 'why_here');
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
        $this->assertEquals('1001', $saved->getGroupAuthorId());

        // update
        $group->setGroupData(
            $group->getGroupId(),
            $group->getGroupName(),
            'WheĐn yoĐu dđo nođt knđow',
            '1002',
            777,
            ['32', '15', '21', '0']
        );
        $lib->updateGroup($group);

        // check data - again with new values
        $saved = $lib->getGroupDataOnly($group->getGroupId());
        $this->assertEquals('When you do not know', $saved->getGroupDesc()); // overwrite this
        $this->assertEquals('1001', $saved->getGroupAuthorId()); // cannot overwrite this
        $this->assertEquals(['32', '15', '21'], $saved->getGroupParents()); // will be filtered

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
        $lib->deleteGroup('1');
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
        $this->assertEquals('1000', $data[1]->getGroupAuthorId());
    }

    protected function wantedGroup($name = 'another'): FileGroup
    {
        $user = new FileGroup();
        $user->setGroupData('3', $name,'Testing group', '1001',  666);
        return $user;
    }
}
