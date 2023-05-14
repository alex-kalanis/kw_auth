<?php

namespace TraitsTests;


use kalanis\kw_auth\Traits\TSeparated;


class SeparatedTest extends \CommonTestClass
{
    /**
     * @param mixed $in
     * @param int[] $result
     * @dataProvider filterDataProvider
     */
    public function testSeparate($in, array $result): void
    {
        $lib = new XSeparated();
        $this->assertEquals($result, $lib->separateStr($in));
    }

    public function filterDataProvider(): array
    {
        return [
            ['Just for unable to split', ['Just for unable to split']],
            ['there is, separated string, contains data', ['there is', ' separated string', ' contains data']],
            ['there is, separated string,123,which contains numbers,456.789', ['there is', ' separated string', '123', 'which contains numbers', '456.789']],
            ['123,456', ['123', '456']],
            ['123.456', ['123.456'], ],
        ];
    }

    /**
     * @param int[] $in
     * @param string $out
     * @dataProvider compactProvider
     */
    public function testCompact(array $in, string $out): void
    {
        $lib = new XSeparated();
        $this->assertEquals($out, $lib->compactStr($in));
    }

    public function compactProvider(): array
    {
        return [
            [['abc', 'def', 'ghi', 'jkl'], 'abc,def,ghi,jkl'],
            [[1, 2, 3, 4], '1,2,3,4'],
            [[true, false, null], '1'],
        ];
    }
}


class XSeparated
{
    use TSeparated;
}
