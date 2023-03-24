<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_auth\Sources\TStatusTransform;
use kalanis\kw_auth\Sources\TUserStatuses;


class StatusTest extends CommonTestClass
{
    /**
     * @param int|null $what
     * @dataProvider statusesProvider
     */
    public function testOriginal(?int $what): void
    {
        $lib = new XStatusTransform();
        $this->assertEquals($what, $lib->to($lib->from($what)));
    }

    public function statusesProvider(): array
    {
        return [
            [1, ],
            [22, ],
            [null, ],
        ];
    }

    public function testList(): void
    {
        $lib = new XStatusList();
        $this->assertNotEmpty($lib->readUserStatuses());
    }
}


class XStatusTransform
{
    use TStatusTransform;

    public function from(?int $value): string
    {
        return $this->transformFromIntToString($value);
    }

    public function to(string $value): ?int
    {
        return $this->transformFromStringToInt($value);
    }
}


class XStatusList
{
    use TUserStatuses;
}
