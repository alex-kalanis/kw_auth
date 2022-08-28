<?php

namespace kalanis\kw_auth\Sources\Files;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Interfaces;
use kalanis\kw_auth\Sources\TClasses;
use kalanis\kw_auth\Sources\TExpiration;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class AFiles
 * @package kalanis\kw_auth\Sources
 * Authenticate via multiple files
 * Combined one - failing with no survivors!
 */
abstract class AFiles implements Interfaces\IAuthCert, Interfaces\IAccessGroups, Interfaces\IAccessClasses
{
    use TClasses;
    use TExpiration;
    use TGroups;
    use TLines;
    use TStore;

    const PW_NAME = 0;
    const PW_ID = 1;
    const PW_GROUP = 2;
    const PW_CLASS = 3;
    const PW_DISPLAY = 4;
    const PW_DIR = 5;
    const PW_FEED = 6;

    const SH_NAME = 0;
    const SH_PASS = 1;
    const SH_CHANGE_LAST = 2;
    const SH_CHANGE_NEXT = 3;
    const SH_CERT_SALT = 4;
    const SH_CERT_KEY = 5;
    const SH_FEED = 6;

    /** @var Interfaces\IMode */
    protected $mode = null;
    /** @var string */
    protected $path = '';

    public function __construct(Interfaces\IMode $mode, ILock $lock, string $dir, ?Interfaces\IKATranslations $lang = null)
    {
        $this->setLang($lang);
        $this->initAuthLock($lock);
        $this->path = $dir;
        $this->mode = $mode;
    }

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        if (empty($params['password'])) {
            throw new AuthException($this->getLang()->kauPassMustBeSet());
        }
        $time = time();
        $name = $this->stripChars($userName);

        // load from shadow
        $this->checkLock();

        try {
            $shadowLines = $this->openShadow();
        } catch (AuthException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($shadowLines as &$line) {
            if (
                ($line[static::SH_NAME] == $name)
                && $this->mode->check(strval($params['password']), strval($line[static::SH_PASS]))
                && ($time < $line[static::SH_CHANGE_NEXT])
            ) {
                $class = $this->getDataOnly($userName);
                if ($class) {
                    $this->setExpirationNotice($class, intval($line[static::SH_CHANGE_NEXT]));
                    return $class;
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
            $passwordLines = $this->openPassword();
        } catch (AuthException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($passwordLines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                $user = $this->getUserClass();
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
        }
        return null;
    }

    protected function getUserClass(): Interfaces\IUser
    {
        return new FileCertUser();
    }

    public function getCertData(string $userName): ?Interfaces\IUserCert
    {
        $name = $this->stripChars($userName);

        // load from shadow
        $this->checkLock();

        try {
            $shadowLines = $this->openShadow();
        } catch (AuthException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($shadowLines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $class = $this->getDataOnly($userName);
                if ($class && ($class instanceof Interfaces\IUserCert)) {
                    $class->addCertInfo(
                        strval(base64_decode(strval($line[static::SH_CERT_KEY]))),
                        strval($line[static::SH_CERT_SALT])
                    );
                    return $class;
                }
            }
        }
        return null;
    }

    public function updatePassword(string $userName, string $passWord): void
    {
        $name = $this->stripChars($userName);
        // load from shadow
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();
        try {
            $lines = $this->openShadow();
        } catch (AuthException $ex) {
            $this->getLock()->delete();
            throw $ex;
        }
        foreach ($lines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $changed = true;
                $line[static::SH_PASS] = $this->mode->hash($passWord);
                $line[static::SH_CHANGE_NEXT] = $this->whenItExpire();
            }
        }
        if ($changed) {
            $this->saveShadow($lines);
        }
        $this->getLock()->delete();
    }

    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): void
    {
        $name = $this->stripChars($userName);
        // load from shadow
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();
        try {
            $lines = $this->openShadow();
        } catch (AuthException $ex) {
            $this->getLock()->delete();
            throw $ex;
        }
        foreach ($lines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $changed = true;
                $line[static::SH_CERT_KEY] = $certKey ? base64_encode($certKey) : $line[static::SH_CERT_KEY];
                $line[static::SH_CERT_SALT] = $certSalt ?: $line[static::SH_CERT_SALT];
            }
        }
        if ($changed) {
            $this->saveShadow($lines);
        }
        $this->getLock()->delete();
    }

    public function createAccount(Interfaces\IUser $user, string $password): void
    {
        $userName = $this->stripChars($user->getAuthName());
        $displayName = $this->stripChars($user->getDisplayName());
        $directory = $this->stripChars($user->getDir());
        $certSalt = '';
        $certKey = '';

        if ($user instanceof Interfaces\IUserCert) {
            $certSalt = $this->stripChars($user->getPubSalt());
            $certKey = $user->getPubKey();
        }

        // not everything necessary is set
        if (empty($userName) || empty($directory) || empty($password)) {
            throw new AuthException($this->getLang()->kauPassMissParam());
        }
        $this->checkLock();

        $uid = Interfaces\IUser::LOWEST_USER_ID;
        $this->getLock()->create();

        // read password
        try {
            $passLines = $this->openPassword();
        } catch (AuthException $ex) {
            $passLines = [];
        }
        foreach ($passLines as &$line) {
            $uid = max($uid, $line[static::PW_ID]);
        }
        $uid++;

        $newUserPass = [
            static::PW_NAME => $userName,
            static::PW_ID => $uid,
            static::PW_GROUP => empty($user->getGroup()) ? $uid : $user->getGroup() ,
            static::PW_CLASS => empty($user->getClass()) ? Interfaces\IAccessClasses::CLASS_USER : $user->getClass() ,
            static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
            static::PW_DIR => $directory,
            static::PW_FEED => '',
        ];
        ksort($newUserPass);
        $passLines[] = $newUserPass;

        // now read shadow
        try {
            $shadeLines = $this->openShadow();
        } catch (AuthException $ex) {
            $shadeLines = [];
        }

        $newUserShade = [
            static::SH_NAME => $userName,
            static::SH_PASS => $this->mode->hash($password),
            static::SH_CHANGE_LAST => time(),
            static::SH_CHANGE_NEXT => $this->whenItExpire(),
            static::SH_CERT_SALT => $certSalt,
            static::SH_CERT_KEY => $certKey ? base64_encode($certKey) : '',
            static::SH_FEED => '',
        ];
        ksort($newUserShade);
        $shadeLines[] = $newUserShade;

        // now save it all
        try {
            $this->savePassword($passLines);
            $this->saveShadow($shadeLines);
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

        $passLines = $this->openPassword();
        $result = [];
        foreach ($passLines as &$line) {
            $record = $this->getUserClass();
            $record->setData(
                intval($line[static::PW_ID]),
                strval($line[static::PW_NAME]),
                intval($line[static::PW_GROUP]),
                intval($line[static::PW_CLASS]),
                strval($line[static::PW_DISPLAY]),
                strval($line[static::PW_DIR])
            );
            $result[] = $record;
        }

        return $result;
    }

    public function updateAccount(Interfaces\IUser $user): void
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        $this->checkLock();

        $this->getLock()->create();
        $oldName = null;
        try {
            $passwordLines = $this->openPassword();
        } catch (AuthException $ex) {
            $this->getLock()->delete();
            throw $ex;
        }
        foreach ($passwordLines as &$line) {
            if (($line[static::PW_NAME] == $userName) && ($line[static::PW_ID] != $user->getAuthId())) {
                $this->getLock()->delete();
                throw new AuthException($this->getLang()->kauPassLoginExists());
            }
            if ($line[static::PW_ID] == $user->getAuthId()) {
                // REFILL
                if (!empty($userName) && $userName != $line[static::PW_NAME]) {
                    $oldName = $line[static::PW_NAME];
                    $line[static::PW_NAME] = $userName;
                }
                $line[static::PW_GROUP] = !empty($user->getGroup()) ? $user->getGroup() : $line[static::PW_GROUP] ;
                $line[static::PW_CLASS] = !empty($user->getClass()) ? $user->getClass() : $line[static::PW_CLASS] ;
                $line[static::PW_DISPLAY] = !empty($displayName) ? $displayName : $line[static::PW_DISPLAY] ;
                $line[static::PW_DIR] = !empty($directory) ? $directory : $line[static::PW_DIR] ;
            }
        }

        try {
            $this->savePassword($passwordLines);

            if (!is_null($oldName)) {
                $lines = $this->openShadow();
                foreach ($lines as &$line) {
                    if ($line[static::SH_NAME] == $oldName) {
                        $line[static::SH_NAME] = $userName;
                    }
                }
                $this->saveShadow($lines);
            }
        } finally {
            $this->getLock()->delete();
        }
    }

    public function deleteAccount(string $userName): void
    {
        $name = $this->stripChars($userName);
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();

        // update password
        try {
            $passLines = $this->openPassword();
        } catch (AuthException $ex) {
            $this->getLock()->delete();
            throw $ex;
        }
        foreach ($passLines as $index => &$line) {
            if ($line[static::PW_NAME] == $name) {
                unset($passLines[$index]);
                $changed = true;
            }
        }

        // now update shadow
        try {
            $shadeLines = $this->openShadow();
        } catch (AuthException $ex) {
            $this->getLock()->delete();
            throw $ex;
        }
        foreach ($shadeLines as $index => &$line) {
            if ($line[static::SH_NAME] == $name) {
                unset($shadeLines[$index]);
                $changed = true;
            }
        }

        // now save it all
        try {
            if ($changed) {
                $this->savePassword($passLines);
                $this->saveShadow($shadeLines);
            }
        } finally {
            $this->getLock()->delete();
        }
    }

    protected function checkRest(int $groupId): void
    {
        $passLines = $this->openPassword();
        foreach ($passLines as &$line) {
            if ($line[static::PW_GROUP] == $groupId) {
                throw new AuthException($this->getLang()->kauGroupHasMembers());
            }
        }
    }

    /**
     * @throws AuthException
     * @return array<int, array<int, string|int>>
     */
    protected function openPassword(): array
    {
        return $this->openFile($this->path . DIRECTORY_SEPARATOR . Interfaces\IFile::PASS_FILE);
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthException
     */
    protected function savePassword(array $lines): void
    {
        $this->saveFile($this->path . DIRECTORY_SEPARATOR . Interfaces\IFile::PASS_FILE, $lines);
    }

    /**
     * @throws AuthException
     * @return array<int, array<int, string|int>>
     */
    protected function openShadow(): array
    {
        return $this->openFile($this->path . DIRECTORY_SEPARATOR . Interfaces\IFile::SHADE_FILE);
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthException
     */
    protected function saveShadow(array $lines): void
    {
        $this->saveFile($this->path . DIRECTORY_SEPARATOR . Interfaces\IFile::SHADE_FILE, $lines);
    }

    /**
     * @throws AuthException
     * @return array<int, array<int, string|int>>
     */
    protected function openGroups(): array
    {
        return $this->openFile($this->path . DIRECTORY_SEPARATOR . Interfaces\IFile::GROUP_FILE);
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthException
     */
    protected function saveGroups(array $lines): void
    {
        $this->saveFile($this->path . DIRECTORY_SEPARATOR . Interfaces\IFile::GROUP_FILE, $lines);
    }
}
