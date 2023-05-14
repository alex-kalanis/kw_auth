<?php

namespace kalanis\kw_auth\Sources\Files;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileUser;
use kalanis\kw_auth\Interfaces;
use kalanis\kw_auth\Sources\TAuthLock;
use kalanis\kw_auth\Sources\TStatusTransform;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class AFile
 * @package kalanis\kw_auth\Sources\Files
 * Authenticate via single file
 */
abstract class AFile implements Interfaces\IAuth, Interfaces\IAccessAccounts
{
    use TAuthLock;
    use TLines;
    use TStatusTransform;
    use TStore;

    const PW_ID = 0;
    const PW_NAME = 1;
    const PW_PASS = 2;
    const PW_GROUP = 3;
    const PW_CLASS = 4;
    const PW_STATUS = 5;
    const PW_DISPLAY = 6;
    const PW_DIR = 7;
    const PW_FEED = 8;

    /** @var Interfaces\IMode */
    protected $mode = null;
    /** @var Interfaces\IStatus */
    protected $status = null;
    /** @var string[] */
    protected $path = [];

    /**
     * @param Interfaces\IMode $mode hashing mode
     * @param Interfaces\IStatus $status which status is necessary to use that feature
     * @param ILock $lock file lock
     * @param string[] $path use full path with file name
     * @param Interfaces\IKauTranslations|null $lang
     */
    public function __construct(Interfaces\IMode $mode, Interfaces\IStatus $status, ILock $lock, array $path, ?Interfaces\IKauTranslations $lang = null)
    {
        $this->setAuLang($lang);
        $this->mode = $mode;
        $this->status = $status;
        $this->path = $path;
        $this->initAuthLock($lock);
    }

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        if (empty($params['password'])) {
            throw new AuthException($this->getAuLang()->kauPassMustBeSet());
        }
        $name = $this->stripChars($userName);
        $pass = strval($params['password']);

        $this->checkLock();
        try {
            $passLines = $this->openFile($this->path);
        } catch (AuthException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($passLines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                if (
                    $this->mode->check($pass, strval($line[static::PW_PASS]))
                    && $this->status->allowLogin($this->transformFromStringToInt(strval($line[static::PW_STATUS])))
                ) {
                    return $this->getUserClass($line);
                }
            }
        }
        return null;
    }

    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        $name = $this->stripChars($userName);

        // load from password
        $this->checkLock();
        try {
            $passwordLines = $this->openFile($this->path);
        } catch (AuthException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($passwordLines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                return $this->getUserClass($line);
            }
        }
        return null;
    }

    /**
     * @param array<int, string|int|float> $line
     * @return Interfaces\IUser
     */
    protected function getUserClass(array &$line): Interfaces\IUser
    {
        $user = new FileUser();
        $user->setUserData(
            strval($line[static::PW_ID]),
            strval($line[static::PW_NAME]),
            strval($line[static::PW_GROUP]),
            intval($line[static::PW_CLASS]),
            $this->transformFromStringToInt(strval($line[static::PW_STATUS])),
            strval($line[static::PW_DISPLAY]),
            strval($line[static::PW_DIR])
        );
        return $user;
    }

    public function createAccount(Interfaces\IUser $user, string $password): void
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        // not everything necessary is set
        if (empty($userName) || empty($directory) || empty($password)) {
            throw new AuthException($this->getAuLang()->kauPassMissParam());
        }
        $this->checkLock();

        $uid = Interfaces\IUser::LOWEST_USER_ID;
        $this->getLock()->create();

        // read password
        try {
            $passLines = $this->openFile($this->path);
        } catch (AuthException $ex) {
            // silence the problems on storage
            $passLines = [];
        }
        foreach ($passLines as &$line) {
            $uid = max($uid, intval($line[static::PW_ID]));
        }
        $uid++;

        $newUserPass = [
            static::PW_ID => strval($uid),
            static::PW_NAME => $userName,
            static::PW_PASS => $this->mode->hash($password),
            static::PW_GROUP => empty($user->getGroup()) ? $uid : $user->getGroup() ,
            static::PW_CLASS => empty($user->getClass()) ? Interfaces\IAccessClasses::CLASS_USER : strval($user->getClass()) ,
            static::PW_STATUS => $this->transformFromIntToString($user->getStatus()),
            static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
            static::PW_DIR => $directory,
            static::PW_FEED => '',
        ];
        ksort($newUserPass);
        $passLines[] = $newUserPass;

        // now save it
        try {
            $this->saveFile($this->path, $passLines);
        } finally {
            $this->getLock()->delete();
        }
    }

    /**
     * @throws AuthException
     * @throws LockException
     * @return Interfaces\IUser[]
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

    public function updateAccount(Interfaces\IUser $user): bool
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        $this->checkLock();

        $this->getLock()->create();
        try {
            $passwordLines = $this->openFile($this->path);
        } finally {
            $this->getLock()->delete();
        }
        foreach ($passwordLines as &$line) {
            if ($line[static::PW_NAME] == $userName) {
                // REFILL
                $line[static::PW_GROUP] = !empty($user->getGroup()) ? $user->getGroup() : $line[static::PW_GROUP] ;
                $line[static::PW_CLASS] = !empty($user->getClass()) ? strval($user->getClass()) : $line[static::PW_CLASS] ;
                $line[static::PW_STATUS] = $this->transformFromIntToString($user->getStatus());
                $line[static::PW_DISPLAY] = !empty($displayName) ? $displayName : $line[static::PW_DISPLAY] ;
                $line[static::PW_DIR] = !empty($directory) ? $directory : $line[static::PW_DIR] ;
            }
        }

        try {
            $this->saveFile($this->path, $passwordLines);
        } finally {
            $this->getLock()->delete();
        }
        return true;
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        $name = $this->stripChars($userName);
        // load from shadow
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();

        try {
            $lines = $this->openFile($this->path);
        } finally {
            $this->getLock()->delete();
        }
        foreach ($lines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                $changed = true;
                $line[static::PW_PASS] = $this->mode->hash($passWord);
            }
        }
        try {
            if ($changed) {
                $this->saveFile($this->path, $lines);
            }
        } finally {
            $this->getLock()->delete();
        }
        return true;
    }

    public function deleteAccount(string $userName): bool
    {
        $name = $this->stripChars($userName);
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();

        // update password
        try {
            $passLines = $this->openFile($this->path);
        } catch (AuthException $ex) {
            // removal on non-existent file is not possible and not necessary
            $this->getLock()->delete();
            return true;
        }

        foreach ($passLines as $index => &$line) {
            if ($line[static::PW_NAME] == $name) {
                unset($passLines[$index]);
                $changed = true;
            }
        }

        // now save it all
        try {
            if ($changed) {
                $this->saveFile($this->path, $passLines);
            }
        } finally {
            $this->getLock()->delete();
        }
        return true;
    }
}
