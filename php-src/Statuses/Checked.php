<?php

namespace kalanis\kw_auth\Statuses;


use kalanis\kw_auth\Interfaces;


/**
 * Class Checked
 * @package kalanis\kw_auth\Statuses
 * Authenticate - is allowed to use any available method?
 */
class Checked implements Interfaces\IStatus
{
    public function allowLogin(?int $status): bool
    {
        return is_int($status) && in_array($status, [
            Interfaces\IUser::USER_STATUS_ENABLED,
            Interfaces\IUser::USER_STATUS_ONLY_LOGIN,
        ]);
    }

    public function allowCert(?int $status): bool
    {
        return is_int($status) && in_array($status, [
            Interfaces\IUser::USER_STATUS_ENABLED,
            Interfaces\IUser::USER_STATUS_ONLY_CERT,
        ]);
    }
}
