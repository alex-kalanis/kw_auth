<?php

namespace SourcesTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Sources\Groups;
use kalanis\kw_locks\LockException;


class GroupsTest extends CommonTestClass
{
    protected $sourcePath = '';

    protected function setUp(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . '.groups';
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testGroupManipulation(): void
    {
        $lib = $this->groupSources();
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
     */
    public function testAllGroups(): void
    {
        $lib = $this->groupSources();
        $data = $lib->readGroup();
        $this->assertEquals('Maintainers', $data[0]->getGroupDesc());
        $this->assertEquals(1000, $data[1]->getGroupAuthorId());
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @return Groups
     * @throws LockException
     */
    protected function groupSources(): Groups
    {
        return new Groups(
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function wantedGroup($name = 'another'): FileGroup
    {
        $user = new FileGroup();
        $user->setData(3, $name, 1001, 'Testing group');
        return $user;
    }
}
