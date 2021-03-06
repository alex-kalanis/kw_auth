<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Interfaces\IAccessClasses;
use kalanis\kw_auth\Interfaces\IAccessGroups;
use kalanis\kw_auth\Interfaces\IAuthCert;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\Interfaces\IKATranslations;
use kalanis\kw_auth\Interfaces\IMode;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_auth\Interfaces\IUserCert;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class Files
 * @package kalanis\kw_auth\Sources
 * Authenticate via files
 */
class Files extends AFile implements IAuthCert, IAccessGroups, IAccessClasses
{
    use TClasses;
    use TExpiration;
    use TGroups;

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

    protected $mode = null;

    public function __construct(IMode $mode, ILock $lock, string $dir, ?IKATranslations $lang = null)
    {
        $this->setLang($lang);
        $this->initAuthLock($lock);
        $this->path = $dir;
        $this->mode = $mode;
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        if (empty($params['password'])) {
            throw new AuthException($this->getLang()->kauPassMustBeSet());
        }
        $time = time();
        $name = $this->stripChars($userName);

        // load from shadow
        $this->checkLock();

        $shadowLines = $this->openShadow();
        foreach ($shadowLines as &$line) {
            if (
                ($line[static::SH_NAME] == $name)
                && $this->mode->check((string)$params['password'], (string)$line[static::SH_PASS])
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

    public function getDataOnly(string $userName): ?IUser
    {
        $name = $this->stripChars($userName);

        // load from password
        $this->checkLock();

        $passwordLines = $this->openPassword();
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

    protected function getUserClass(): IUser
    {
        return new FileCertUser();
    }

    public function getCertData(string $userName): ?IUserCert
    {
        $name = $this->stripChars($userName);

        // load from shadow
        $this->checkLock();

        $shadowLines = $this->openShadow();
        foreach ($shadowLines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $class = $this->getDataOnly($userName);
                if ($class && ($class instanceof IUserCert)) {
                    $class->addCertInfo(
                        (string)base64_decode($line[static::SH_CERT_KEY]),
                        (string)$line[static::SH_CERT_SALT]
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
        $this->lock->create();
        $lines = $this->openShadow();
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
        $this->lock->delete();
    }

    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): void
    {
        $name = $this->stripChars($userName);
        // load from shadow
        $this->checkLock();

        $changed = false;
        $this->lock->create();
        $lines = $this->openShadow();
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
        $this->lock->delete();
    }

    public function createAccount(IUser $user, string $password): void
    {
        $userName = $this->stripChars($user->getAuthName());
        $displayName = $this->stripChars($user->getDisplayName());
        $directory = $this->stripChars($user->getDir());
        $certSalt = '';
        $certKey = '';

        if ($user instanceof IUserCert) {
            $certSalt = $this->stripChars($user->getPubSalt());
            $certKey = $user->getPubKey();
        }

        # no everything need is set
        if (empty($userName) || empty($directory) || empty($password)) {
            throw new AuthException($this->getLang()->kauPassMissParam());
        }
        $this->checkLock();

        $uid = IUser::LOWEST_USER_ID;
        $this->lock->create();

        # read password
        $passLines = $this->openPassword();
        foreach ($passLines as &$line) {
            $uid = max($uid, $line[static::PW_ID]);
        }
        $uid++;

        $newUserPass = [
            static::PW_NAME => $userName,
            static::PW_ID => $uid,
            static::PW_GROUP => empty($user->getGroup()) ? $uid : $user->getGroup() ,
            static::PW_CLASS => empty($user->getClass()) ? IAccessClasses::CLASS_USER : $user->getClass() ,
            static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
            static::PW_DIR => $directory,
            static::PW_FEED => '',
        ];
        ksort($newUserPass);
        $passLines[] = $newUserPass;

        # now read shadow
        $shadeLines = $this->openShadow();

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

        # now save it
        $this->savePassword($passLines);
        $this->saveShadow($shadeLines);

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

    public function updateAccount(IUser $user): void
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        $this->checkLock();

        $this->lock->create();
        $oldName = null;
        $passwordLines = $this->openPassword();
        foreach ($passwordLines as &$line) {
            if (($line[static::PW_NAME] == $userName) && ($line[static::PW_ID] != $user->getAuthId())) {
                $this->lock->delete();
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
        $this->lock->delete();
    }

    public function deleteAccount(string $userName): void
    {
        $name = $this->stripChars($userName);
        $this->checkLock();

        $changed = false;
        $this->lock->create();

        # update password
        $passLines = $this->openPassword();
        foreach ($passLines as $index => &$line) {
            if ($line[static::PW_NAME] == $name) {
                unset($passLines[$index]);
                $changed = true;
            }
        }

        # now update shadow
        $shadeLines = $this->openShadow();
        foreach ($shadeLines as $index => &$line) {
            if ($line[static::SH_NAME] == $name) {
                unset($shadeLines[$index]);
                $changed = true;
            }
        }

        # now save it
        if ($changed) {
            $this->savePassword($passLines);
            $this->saveShadow($shadeLines);
        }
        $this->lock->delete();
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
     * @return string[][]
     * @throws AuthException
     */
    protected function openPassword(): array
    {
        return $this->openFile($this->path . DIRECTORY_SEPARATOR . IFile::PASS_FILE);
    }

    /**
     * @param string[][] $lines
     * @throws AuthException
     */
    protected function savePassword(array $lines): void
    {
        $this->saveFile($this->path . DIRECTORY_SEPARATOR . IFile::PASS_FILE, $lines);
    }

    /**
     * @return string[][]
     * @throws AuthException
     */
    protected function openShadow(): array
    {
        return $this->openFile($this->path . DIRECTORY_SEPARATOR . IFile::SHADE_FILE);
    }

    /**
     * @param string[][] $lines
     * @throws AuthException
     */
    protected function saveShadow(array $lines): void
    {
        $this->saveFile($this->path . DIRECTORY_SEPARATOR . IFile::SHADE_FILE, $lines);
    }

    /**
     * @return string[][]
     * @throws AuthException
     */
    protected function openGroups(): array
    {
        return $this->openFile($this->path . DIRECTORY_SEPARATOR . IFile::GROUP_FILE);
    }

    /**
     * @param string[][] $lines
     * @throws AuthException
     */
    protected function saveGroups(array $lines): void
    {
        $this->saveFile($this->path . DIRECTORY_SEPARATOR . IFile::GROUP_FILE, $lines);
    }
}
