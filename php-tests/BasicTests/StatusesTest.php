<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_auth\Statuses;


class StatusesTest extends CommonTestClass
{
    public function testAlways(): void
    {
        $lib = new Statuses\Always();
        $this->assertTrue($lib->allowLogin(null));
        $this->assertTrue($lib->allowLogin(12345));
        $this->assertTrue($lib->allowCert(null));
        $this->assertTrue($lib->allowCert(67890));
    }

    /**
     * @param bool $loginResult
     * @param bool $certResult
     * @param int|null $what
     * @dataProvider statusesProvider
     */
    public function testCheckedLogin(bool $loginResult, bool $certResult, ?int $what): void
    {
        $lib = new Statuses\Checked();
        $this->assertEquals($loginResult, $lib->allowLogin($what));
    }

    /**
     * @param bool $loginResult
     * @param bool $certResult
     * @param int|null $what
     * @dataProvider statusesProvider
     */
    public function testCheckedCert(bool $loginResult, bool $certResult, ?int $what): void
    {
        $lib = new Statuses\Checked();
        $this->assertEquals($certResult, $lib->allowCert($what));
    }

    public function statusesProvider(): array
    {
        return [
            [false, false, IUser::USER_STATUS_UNKNOWN, ],
            [false, false, IUser::USER_STATUS_DISABLED, ],
            [true,  true,  IUser::USER_STATUS_ENABLED, ],
            [true,  false, IUser::USER_STATUS_ONLY_LOGIN, ],
            [false, true,  IUser::USER_STATUS_ONLY_CERT, ],
            [false, false, 9999, ],
        ];
    }
}
