<?php

namespace SourcesTests\Files\Files;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Sources\Files\Files\Groups;
use kalanis\kw_files\Access;
use kalanis\kw_locks\LockException;
use kalanis\kw_storage\Storage\Key\DefaultKey;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target\Memory;
use kalanis\kw_storage\StorageException;


class GroupsTest extends CommonTestClass
{
    protected $sourcePath = '.groups';

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testNotExistsData(): void
    {
        $lib = $this->emptyGroupSources();
        $this->assertNull($lib->getGroupDataOnly(15));
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testCreateGroupOnEmptyInstance(): void
    {
        $lib = $this->emptyGroupSources();
        $group = $this->wantedGroup();

        // create
        $lib->createGroup($group);
        // check data
        $saved = $lib->getGroupDataOnly(1);
        $this->assertEquals('another', $saved->getGroupName());
        $this->assertEquals('Testing group', $saved->getGroupDesc());
        $this->assertEquals(1001, $saved->getGroupAuthorId());
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testUpdateGroupOnEmptyInstance(): void
    {
        $lib = $this->emptyGroupSources();
        $group = $this->wantedGroup();

        // update
        $this->expectException(AuthException::class);
        $lib->updateGroup($group);
    }

    /**
     * @throws AuthException
     * @throws LockException
     * @throws StorageException
     */
    public function testGroupManipulation(): void
    {
        $lib = $this->groupSources();
        $group = $this->wantedGroup();

        // create
        $lib->createGroup($group);
        // check data
        $saved = $lib->getGroupDataOnly(3);
        $this->assertEquals('another', $saved->getGroupName());
        $this->assertEquals('Testing group', $saved->getGroupDesc());
        $this->assertEquals(1001, $saved->getGroupAuthorId());

        // update
        $group->setData(
            $group->getGroupId(),
            $group->getGroupName(),
            1002,
            'WheĐn yoĐu dđo nođt knđow',
            999
        );
        $lib->updateGroup($group);

        // check data - again with new values
        $saved = $lib->getGroupDataOnly(3);
        $this->assertEquals('When you do not know', $saved->getGroupDesc()); // overwrite this
        $this->assertEquals(1001, $saved->getGroupAuthorId()); // cannot overwrite this

        // remove
        $lib->deleteGroup($group->getGroupId());
        // check for existence
        $this->assertEmpty($lib->getGroupDataOnly(3));
    }

    /**
     * @throws AuthException
     * @throws LockException
     * @throws StorageException
     */
    public function testCreateFail(): void
    {
        $lib = $this->groupSources();
        $group = $this->wantedGroup('');
        $this->expectException(AuthException::class);
        $lib->createGroup($group);
    }

    /**
     * @throws AuthException
     * @throws LockException
     * @throws StorageException
     */
    public function testAllGroups(): void
    {
        $lib = $this->groupSources();
        $data = $lib->readGroup();
        $this->assertEquals('Maintainers', $data[0]->getGroupDesc());
        $this->assertEquals(1000, $data[1]->getGroupAuthorId());
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testRemoveGroupOnEmptyInstance(): void
    {
        $lib = $this->emptyGroupSources();

        // delete
        $lib->deleteGroup(41);
        $this->assertNull($lib->getGroupDataOnly(41));
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testCreateStorageFail(): void
    {
        $lib = $this->failedGroupSources();
        $group = $this->wantedGroup('');
        $this->expectException(AuthException::class);
        $lib->createGroup($group);
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testCreateStorageFailSave(): void
    {
        $lib = $this->failedGroupSources(true);
        $group = $this->wantedGroup('');
        $this->expectException(AuthException::class);
        $lib->createGroup($group);
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testReadGroupsStorageFail(): void
    {
        $lib = $this->failedGroupSources();
        $this->expectException(AuthException::class);
        $lib->readGroup();
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testUpdateGroupStorageFail(): void
    {
        $lib = $this->failedGroupSources();
        $this->expectException(AuthException::class);
        $lib->updateGroup($this->wantedGroup());
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testUpdateGroupStorageFailSave(): void
    {
        $lib = $this->failedGroupSources(true);
        $this->expectException(AuthException::class);
        $lib->updateGroup($this->wantedGroup());
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testRemoveGroupStorageFail(): void
    {
        $lib = $this->failedGroupSources();
        $this->assertFalse($lib->deleteGroup(41));
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testRemoveGroupStorageFailSave(): void
    {
        $lib = $this->failedGroupSources(true, '0:all:1000:Main:1:' . "\r\n" . '2:folks:1000:Dirt:1:' . "\r\n");
        $this->expectException(AuthException::class);
        $lib->deleteGroup(2);
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @throws StorageException
     * @return Groups
     */
    protected function groupSources(): Groups
    {
        $storage = new Storage(new DefaultKey(), new Memory());
        $file = new Groups(
            (new Access\Factory())->getClass($storage),
            $this->getLockPath(),
            $this->sourcePath
        );
        $storage->write($this->sourcePath,
            '0:root:1000:Maintainers:1:' . "\r\n"
            . '1:admin:1000:Administrators:1:' . "\r\n"
            . '# commented out' . "\r\n"
            . '2:user:1000:All users:1:' . "\r\n"
            // last line is intentionally empty one
        );
        return $file;
    }

    /**
     * @throws LockException
     * @return Groups
     */
    protected function emptyGroupSources(): Groups
    {
        return new Groups(
            (new Access\Factory())->getClass(new Storage(new DefaultKey(), new Memory())),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * @param bool $canOpen
     * @param string $content
     * @throws LockException
     * @return Groups
     */
    protected function failedGroupSources(bool $canOpen = false, string $content = ''): Groups
    {
        return new Groups(
            (new Access\Factory())->getClass(new \XFailedStorage($canOpen, $content)),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function wantedGroup($name = 'another'): FileGroup
    {
        $user = new FileGroup();
        $user->setData(3, $name, 1001, 'Testing group', 888);
        return $user;
    }
}
