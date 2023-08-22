<?php

namespace TraitsTests;


use CommonTestClass;
use kalanis\kw_auth\Traits;


class StampTest extends CommonTestClass
{
    /**
     * @param int $when
     * @param int $length
     * @param bool $pass
     * @dataProvider stampsProvider
     */
    public function testStamps(int $when, int $length, bool $pass): void
    {
        $lib = new MockStamp();
        $lib->setDiff($length);
        $this->assertEquals($pass, $lib->checkTime($when));
    }

    public function stampsProvider(): array
    {
        return [
            [ time() + 10, 20, true],
            [ time() - 10, 20, true],
            [ time() + 50, 20, false],
            [ time() - 50, 20, false],
        ];
    }
}


class MockStamp
{
    use Traits\TStamp;

    public function setDiff(int $timeDiff): void
    {
        $this->initStamp($timeDiff);
    }

    public function checkTime(int $time): bool
    {
        return $this->checkStamp($time);
    }
}
