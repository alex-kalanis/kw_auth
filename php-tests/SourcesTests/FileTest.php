<?php

namespace SourcesTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Sources\File;
use kalanis\kw_locks\LockException;


class FileTest extends CommonTestClass
{
    protected $sourcePath = '';

    protected function setUp(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '.passcomb';
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
        $user = $lib->authenticate('manager', ['password' => 'thisisreal']);
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
     * @return File
     * @throws LockException
     */
    protected function fileSources(): File
    {
        return new File(
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function wantedUser(): FileUser
    {
        $user = new FileUser();
        $user->setData(600, 'another', 0, 0, 'Testing another', 'why_here');
        return $user;
    }
}
