<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Interfaces\IAccessAccounts;
use kalanis\kw_auth\Interfaces\IAccessClasses;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_extras\Lock;


/**
 * Class File
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via file
 * @codeCoverageIgnore because access external content
 */
class File implements IAuth, IAccessAccounts
{
    use TFiles;
    use TLines;

    const PW_ID = 0;
    const PW_NAME = 1;
    const PW_PASS = 2;
    const PW_GROUP = 3;
    const PW_CLASS = 4;
    const PW_DISPLAY = 5;
    const PW_DIR = 6;

    protected $lock = null;
    protected $path = '';

    public function __construct(string $path)
    {
        $this->path = $path;
        try {
            $this->lock = new Lock($this->path . IFile::LOCK_FILE );
        } catch (\Exception $ex) {
            throw new AuthException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        if (empty($params['password'])) {
            throw new AuthException('You must set the password to check!');
        }
        $name = $this->stripChars($userName);
        $pass = $params['password'];

        // load from passwd
        if ($this->lock->isAnotherLock()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }
        $passLines = $this->openFile($this->path);
        foreach ($passLines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                if (password_verify($pass, $line[static::PW_PASS])) {
                    return $this->getUserClass($line);
                }
            }
        }
        return null;
    }

    public function getDataOnly(string $userName): ?IUser
    {
        $name = $this->stripChars($userName);

        // load from password
        if ($this->lock->isAnotherLock()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }
        $passwordLines = $this->openFile($this->path);
        foreach ($passwordLines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                return $this->getUserClass($line);
            }
        }
        return null;
    }

    protected function getUserClass(array &$line): IUser
    {
        $user = new FileUser();
        $user->setData(
            intval($line[static::PW_ID]),
            strval($line[static::PW_NAME]),
            intval($line[static::PW_GROUP]),
            intval($line[static::PW_CLASS]),
            strval($line[static::PW_DISPLAY]),
            strval($line[static::PW_DIR])
        );
        return $user;
    }

    public function createAccount(IUser $user, string $password): void
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        # no everything need is set
        if (empty($userName) || empty($directory) || empty($password)) {
            throw new AuthException('MISSING_NECESSARY_PARAMS');
        }
        if ($this->lock->isAnotherLock()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $uid = IUser::LOWEST_USER_ID;
        $this->lock->exclusiveLock();

        # read password
        $passLines = $this->openFile($this->path);
        foreach ($passLines as &$line) {
            $uid = max($uid, $line[static::PW_ID]);
        }
        $uid++;

        $newUserPass = [
            static::PW_ID => $uid,
            static::PW_NAME => $userName,
            static::PW_PASS => password_hash($password, PASSWORD_DEFAULT),
            static::PW_GROUP => empty($user->getGroup()) ? $uid : $user->getClass() ,
            static::PW_CLASS => empty($user->getClass()) ? IAccessClasses::CLASS_USER : $user->getClass() ,
            static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
            static::PW_DIR => $directory,
        ];
        ksort($newUserPass);
        $passLines[] = $newUserPass;

        # now save it
        $this->saveFile($this->path, $passLines);

        $this->lock->closeLock();
    }

    public function readAccounts(): array
    {
        if ($this->lock->isAnotherLock()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $passLines = $this->openFile($this->path);
        $result = [];
        foreach ($passLines as &$line) {
            $result[] = $this->getUserClass($line);
        }

        return $result;
    }

    public function updateAccount(IUser $user): void
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        if ($this->lock->isAnotherLock()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $this->lock->exclusiveLock();
        $passwordLines = $this->openFile($this->path);
        foreach ($passwordLines as &$line) {
            if ($line[static::PW_NAME] == $userName) {
                // REFILL
                $line[static::PW_GROUP] = !empty($user->getGroup()) ? $user->getGroup() : $line[static::PW_GROUP] ;
                $line[static::PW_CLASS] = !empty($user->getClass()) ? $user->getClass() : $line[static::PW_CLASS] ;
                $line[static::PW_DISPLAY] = !empty($displayName) ? $displayName : $line[static::PW_DISPLAY] ;
                $line[static::PW_DIR] = !empty($directory) ? $directory : $line[static::PW_DIR] ;
            }
        }

        $this->saveFile($this->path, $passwordLines);
        $this->lock->closeLock();
    }

    public function updatePassword(string $userName, string $passWord): void
    {
        $name = $this->stripChars($userName);
        // load from shadow
        if ($this->lock->isAnotherLock()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $changed = false;
        $this->lock->exclusiveLock();

        $lines = $this->openFile($this->path);
        foreach ($lines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                $changed = true;
                $line[static::PW_PASS] = password_hash($passWord, PASSWORD_DEFAULT);
            }
        }
        if ($changed) {
            $this->saveFile($this->path, $lines);
        }
        $this->lock->closeLock();
    }

    public function deleteAccount(string $userName): void
    {
        $name = $this->stripChars($userName);
        if ($this->lock->isAnotherLock()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $changed = false;
        $this->lock->exclusiveLock();

        # update password
        $passLines = $this->openFile($this->path);
        foreach ($passLines as $index => &$line) {
            if ($line[static::PW_NAME] == $name) {
                unset($passLines[$index]);
                $changed = true;
            }
        }

        # now save it
        if ($changed) {
            $this->saveFile($this->path, $passLines);
        }
        $this->lock->closeLock();
    }
}
