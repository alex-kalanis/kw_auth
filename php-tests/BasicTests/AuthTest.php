<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_address_handler\Handler;
use kalanis\kw_address_handler\Sources as HandlerSources;
use kalanis\kw_auth\Auth;
use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\AuthTree;
use kalanis\kw_auth\Methods;
use kalanis\kw_auth\Sources;
use kalanis\kw_locks\LockException;


class AuthTest extends CommonTestClass
{
    public function testStatical(): void
    {
        $this->assertEmpty(Auth::getTree());
        Auth::fill(
            new Methods\Everytime(null, null)
        );
        $this->assertNotEmpty(Auth::getTree());

        $this->assertEmpty(Auth::getAuthenticator());
        Auth::setAuthenticator('pass auth class like IAuth to module space');
        $this->assertNotEmpty(Auth::getAuthenticator());
    }

    /**
     * @throws AuthException
     * @throws LockException
     */
    public function testTree(): void
    {
        $tree = new AuthTree();
        $this->assertEmpty($tree->getMethod());

        // this is what should be in bootstrap
        $tree->setTree(
            new Methods\HttpDigest(
                $this->fileSources(),
                new Methods\Everytime(null, null),
                new \MockCredentials([
                    Methods\HttpDigest::INPUT_METHOD => 'PUT',
                    Methods\HttpDigest::INPUT_DIGEST => '0123456789qwertzuiopasdfghjklyxcvbnm--',
                ])
            )
        );

        // now run that
        $this->assertEmpty($tree->getMethod());
        $tree->findMethod(new \MockCredentials());
        $this->assertNotEmpty($tree->getMethod());
        $this->assertTrue($tree->getMethod()->isAuthorized());
        $this->assertEquals('Debug', $tree->getMethod()->getLoggedUser()->getAuthName());

        // tree with data from url
        $tree->setTree(new Methods\UrlCerts(
            $this->fileSources(),
            null,
            new Handler(new HandlerSources\Address('//abcdef/ghi/jkl'))
        ));
        $this->assertEmpty($tree->getMethod());
        $tree->findMethod(new \MockCredentials());
        $this->assertEmpty($tree->getMethod());
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @return Sources\Files
     * @throws LockException
     */
    protected function fileSources(): Sources\Files
    {
        return new Sources\Files(
            $this->getLockPath(),
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data',
            'yxcvbnmasdfghjklqwertzuiop0123456789'
        );
    }
}
