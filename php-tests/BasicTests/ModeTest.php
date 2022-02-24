<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Mode;


class ModeTest extends CommonTestClass
{
    /**
     * @param string $what
     * @throws AuthException
     * @dataProvider passwordsProvider
     */
    public function testOriginal(string $what): void
    {
        $lib = new Mode\KwOrig('asdfghkl123qweqrtziop456yxcvbnm789');
        $this->assertTrue($lib->check($what, $lib->hash($what)));
    }

    /**
     * @param string $what
     * @throws AuthException
     * @dataProvider passwordsProvider
     */
    public function testMd5(string $what): void
    {
        $lib = new Mode\Md5();
        $this->assertTrue($lib->check($what, $lib->hash($what)));
    }

    /**
     * @param string $what
     * @throws AuthException
     * @dataProvider passwordsProvider
     */
    public function testCore(string $what): void
    {
        $lib = new Mode\CoreLib();
        $this->assertTrue($lib->check($what, $lib->hash($what)));
    }

    public function passwordsProvider(): array
    {
        return [
            ['okmijnuhb', ],
            ['wsxedcrfv', ],
        ];
    }
}
