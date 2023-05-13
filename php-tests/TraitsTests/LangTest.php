<?php

namespace TraitsTests;


use CommonTestClass;
use kalanis\kw_auth\Interfaces\IKauTranslations;
use kalanis\kw_auth\Traits\TLang;
use kalanis\kw_auth\Translations;


class LangTest extends CommonTestClass
{
    public function testSimple(): void
    {
        $lib = new XLang();
        $this->assertNotEmpty($lib->getAuLang());
        $this->assertInstanceOf(Translations::class, $lib->getAuLang());
        $lib->setAuLang(new XTrans());
        $this->assertInstanceOf(XTrans::class, $lib->getAuLang());
        $lib->setAuLang(null);
        $this->assertInstanceOf(Translations::class, $lib->getAuLang());
    }
}


class XLang
{
    use TLang;
}


class XTrans implements IKauTranslations
{
    public function kauPassFileNotFound(string $path): string
    {
        return 'mock';
    }

    public function kauPassFileNotSave(string $path): string
    {
        return 'mock';
    }

    public function kauPassMustBeSet(): string
    {
        return 'mock';
    }

    public function kauPassMissParam(): string
    {
        return 'mock';
    }

    public function kauPassLoginExists(): string
    {
        return 'mock';
    }

    public function kauLockSystemNotSet(): string
    {
        return 'mock';
    }

    public function kauAuthAlreadyOpen(): string
    {
        return 'mock';
    }

    public function kauGroupMissParam(): string
    {
        return 'mock';
    }

    public function kauGroupHasMembers(): string
    {
        return 'mock';
    }

    public function kauHashFunctionNotFound(): string
    {
        return 'mock';
    }

    public function kauBanWantedUser(): string
    {
        return 'mock';
    }

    public function kauTooManyTries(): string
    {
        return 'mock';
    }
}