<?php

namespace SourcesTests\Files\Volume;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Sources\Files\Volume\File;
use kalanis\kw_auth\Statuses\Always;
use kalanis\kw_locks\LockException;


class FileTest extends CommonTestClass
{
    protected $sourcePath = [];

    protected function setUp(): void
    {
        $this->sourcePath = array_merge(explode(DIRECTORY_SEPARATOR, __DIR__), ['..', '..', '..', 'data', '.passcomb']);
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
        $user->setUserData(
            null,
            null,
            null,
            2,
            444,
            'WheĐn yoĐu dđo nođt knđow',
            null
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
     * @throws LockException
     * @return File
     */
    protected function fileSources(): File
    {
        return new File(
            new \MockModes(),
            new Always(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function wantedUser(): FileUser
    {
        $user = new FileUser();
        $user->setUserData('600', 'another', '0', 0, 555, 'Testing another', 'why_here');
        return $user;
    }
}
