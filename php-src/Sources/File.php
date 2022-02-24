<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Interfaces\IAccessAccounts;
use kalanis\kw_auth\Interfaces\IAccessClasses;
use kalanis\kw_auth\Interfaces\IAuth;
use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class File
 * @package kalanis\kw_auth\Sources
 * Authenticate via file
 */
class File extends AFile implements IAuth, IAccessAccounts
{
    use TAuthLock;

    const PW_ID = 0;
    const PW_NAME = 1;
    const PW_PASS = 2;
    const PW_GROUP = 3;
    const PW_CLASS = 4;
    const PW_DISPLAY = 5;
    const PW_DIR = 6;
    const PW_FEED = 7;

    protected $mode = null;

    /**
     * @param IMode $mode hashing mode
     * @param ILock $lock file lock
     * @param string $path use full path with file name
     * @param IKATranslations|null $lang
     */
    public function __construct(IMode $mode, ILock $lock, string $path, ?IKATranslations $lang = null)
    {
        $this->setLang($lang);
        $this->mode = $mode;
        $this->path = $path;
        $this->initAuthLock($lock);
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        if (empty($params['password'])) {
            throw new AuthException($this->getLang()->kauPassMustBeSet());
        }
        $name = $this->stripChars($userName);
        $pass = $params['password'];

        $this->checkLock();
        $passLines = $this->openFile($this->path);
        foreach ($passLines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                if ($this->mode->check((string)$pass, (string)$line[static::PW_PASS])) {
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
        $this->checkLock();
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
            throw new AuthException($this->getLang()->kauPassMissParam());
        }
        $this->checkLock();

        $uid = IUser::LOWEST_USER_ID;
        $this->lock->create();

        # read password
        $passLines = $this->openFile($this->path);
        foreach ($passLines as &$line) {
            $uid = max($uid, $line[static::PW_ID]);
        }
        $uid++;

        $newUserPass = [
            static::PW_ID => $uid,
            static::PW_NAME => $userName,
            static::PW_PASS => $this->mode->hash($password),
            static::PW_GROUP => empty($user->getGroup()) ? $uid : $user->getClass() ,
            static::PW_CLASS => empty($user->getClass()) ? IAccessClasses::CLASS_USER : $user->getClass() ,
            static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
            static::PW_DIR => $directory,
            static::PW_FEED => '',
        ];
        ksort($newUserPass);
        $passLines[] = $newUserPass;

        # now save it
        $this->saveFile($this->path, $passLines);

        $this->lock->delete();
    }

    /**
     * @return IUser[]
     * @throws AuthException
     * @throws LockException
     */
    public function readAccounts(): array
    {
        $this->checkLock();

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

        $this->checkLock();

        $this->lock->create();
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
        $this->lock->delete();
    }

    public function updatePassword(string $userName, string $passWord): void
    {
        $name = $this->stripChars($userName);
        // load from shadow
        $this->checkLock();

        $changed = false;
        $this->lock->create();

        $lines = $this->openFile($this->path);
        foreach ($lines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                $changed = true;
                $line[static::PW_PASS] = $this->mode->hash($passWord);
            }
        }
        if ($changed) {
            $this->saveFile($this->path, $lines);
        }
        $this->lock->delete();
    }

    public function deleteAccount(string $userName): void
    {
        $name = $this->stripChars($userName);
        $this->checkLock();

        $changed = false;
        $this->lock->create();

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
        $this->lock->delete();
    }
}
