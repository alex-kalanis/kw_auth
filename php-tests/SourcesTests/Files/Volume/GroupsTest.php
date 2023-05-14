<?php

namespace SourcesTests\Files\Volume;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Sources\Files\Volume\Groups;
use kalanis\kw_locks\LockException;


class GroupsTest extends CommonTestClass
{
    protected $sourcePath = [];

    protected function setUp(): void
    {
        $this->sourcePath = array_merge(explode(DIRECTORY_SEPARATOR, __DIR__), ['..', '..', '..', 'data', '.groups']);
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
        $this->assertEquals('1001', $saved->getGroupAuthorId());

        // update
        $group->setGroupData(
            $group->getGroupId(),
            $group->getGroupName(),
            'WheĐn yoĐu dđo nođt knđow',
            '1002',
            999
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
        $this->assertEquals('1000', $data[1]->getGroupAuthorId());
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Groups
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
        $user->setGroupData('3', $name, 'Testing group', '1001', 888);
        return $user;
    }
}
