<?php

namespace kalanis\kw_auth\Sources;


use kalanis\kw_auth\AuthException;
use kalanis\kw_auth\Data\FileCertUser;
use kalanis\kw_auth\Data\FileGroup;
use kalanis\kw_auth\Interfaces\IAccessClasses;
use kalanis\kw_auth\Interfaces\IAccessGroups;
use kalanis\kw_auth\Interfaces\IAuthCert;
use kalanis\kw_auth\Interfaces\IExpiry;
use kalanis\kw_auth\Interfaces\IFile;
use kalanis\kw_auth\Interfaces\IGroup;
use kalanis\kw_auth\Interfaces\IUser;
use kalanis\kw_auth\Interfaces\IUserCert;
use kalanis\kw_locks\Interfaces\ILock;


/**
 * Class Files
 * @package kalanis\kw_auth\AuthMethods
 * Authenticate via files
 * @codeCoverageIgnore because access external content
 */
class Files implements IAuthCert, IAccessGroups, IAccessClasses
{
    use TFiles;
    use TLines;

    const PW_NAME = 0;
    const PW_ID = 1;
    const PW_GROUP = 2;
    const PW_CLASS = 3;
    const PW_DISPLAY = 4;
    const PW_DIR = 5;

    const SH_NAME = 0;
    const SH_PASS = 1;
    const SH_CHANGE_LAST = 2;
    const SH_CHANGE_NEXT = 3;
    const SH_CERT_SALT = 4;
    const SH_CERT_KEY = 5;

    const GRP_ID = 0;
    const GRP_NAME = 1;
    const GRP_AUTHOR = 2;
    const GRP_DESC = 3;

    protected $lock = null;
    protected $path = '';
    protected $salt = '';
    protected $changeInterval = 31536000; // 60×60×24×365 - one year
    protected $changeNotice = 2592000; // 60×60×24×30 - one month

    /**
     * @param ILock $lock
     * @param string $dir
     * @param string $salt
     * @throws AuthException
     */
    public function __construct(ILock $lock, string $dir, string $salt)
    {
        $this->lock = $lock;
        $this->path = $dir;
        $this->salt = $salt;
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        if (empty($params['password'])) {
            throw new AuthException('You must set the password to check!');
        }
        $time = time();
        $name = $this->stripChars($userName);
        $pass = $this->hashPassword($params['password']);
//var_dump([$userName, $params]);
//var_dump([$name, $pass]);

        // load from shadow
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }
        $shadowLines = $this->openShadow();
        foreach ($shadowLines as &$line) {
            if (($line[static::SH_NAME] == $name) && ($line[static::SH_PASS] == $pass) && ($time < $line[static::SH_CHANGE_NEXT])) {
                $class = $this->getDataOnly($userName);
                if ($class) {
                    if ($class instanceof IExpiry) {
                        $class->setExpiry( $time + $this->changeNotice > $line[static::SH_CHANGE_NEXT] );
                    }
                    return $class;
                }
            }
        }
        return null;
    }

    public function getDataOnly(string $userName): ?IUser
    {
        $name = $this->stripChars($userName);

        // load from passwd
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }
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
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }
        $shadowLines = $this->openShadow();
        foreach ($shadowLines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $class = $this->getDataOnly($userName);
                if ($class && ($class instanceof IUserCert)) {
                    $class->addCertInfo((string)$line[static::SH_CERT_KEY], (string)$line[static::SH_CERT_SALT]);
                    return $class;
                }
            }
        }
        return null;
    }

    /**
     * @param string $userName
     * @param string $passWord
     * @throws AuthException
     */
    public function updatePassword(string $userName, string $passWord): void
    {
        $name = $this->stripChars($userName);
        // load from shadow
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $changed = false;
        $this->lock->create();
        $lines = $this->openShadow();
        foreach ($lines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $changed = true;
                $line[static::SH_PASS] = $this->hashPassword($passWord);
                $line[static::SH_CHANGE_NEXT] = time() + $this->changeInterval;
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
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }
        $changed = false;
        $this->lock->create();
        $lines = $this->openShadow();
        foreach ($lines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $changed = true;
                $line[static::SH_CERT_KEY] = $certKey ?: $line[static::SH_CERT_KEY];
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
            $certKey = $this->stripChars($user->getPubKey());
        }

        # no everything need is set
        if (empty($userName) || empty($directory) || empty($password)) {
            throw new AuthException('MISSING_NECESSARY_PARAMS');
        }
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

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
        ];
        ksort($newUserPass);
        $passLines[] = $newUserPass;

        # now read shadow
        $shadeLines = $this->openShadow();

        $newUserShade = [
            static::SH_NAME => $userName,
            static::SH_PASS => $this->hashPassword($password),
            static::SH_CHANGE_LAST => time(),
            static::SH_CHANGE_NEXT => time() + $this->changeInterval,
            static::SH_CERT_SALT => $certSalt,
            static::SH_CERT_KEY => $certKey,
        ];
        ksort($newUserShade);
        $shadeLines[] = $newUserShade;

        # now save it
        $this->savePassword($passLines);
        $this->saveShadow($shadeLines);

        $this->lock->delete();
    }

    public function readAccounts(): array
    {
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

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

        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $this->lock->create();
        $passwordLines = $this->openPassword();
        foreach ($passwordLines as &$line) {
            if ($line[static::PW_NAME] == $userName) {
                // REFILL
                $line[static::PW_GROUP] = !empty($user->getGroup()) ? $user->getGroup() : $line[static::PW_GROUP] ;
                $line[static::PW_CLASS] = !empty($user->getClass()) ? $user->getClass() : $line[static::PW_CLASS] ;
                $line[static::PW_DISPLAY] = !empty($displayName) ? $displayName : $line[static::PW_DISPLAY] ;
                $line[static::PW_DIR] = !empty($directory) ? $directory : $line[static::PW_DIR] ;
            }
        }

        $this->savePassword($passwordLines);
        $this->lock->delete();
    }

    public function deleteAccount(string $userName): void
    {
        $name = $this->stripChars($userName);
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

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

    public function createGroup(IGroup $group): void
    {
        $userId = $group->getGroupAuthorId();
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        # no everything need is set
        if (empty($userId) || empty($groupName)) {
            throw new AuthException('MISSING_NECESSARY_PARAMS');
        }
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $gid = 0;
        $this->lock->create();

        # read groups
        $groupLines = $this->openGroups();
        foreach ($groupLines as &$line) {
            $gid = max($gid, $line[static::GRP_ID]);
        }
        $gid++;

        $newGroup = [
            static::GRP_ID => $gid,
            static::GRP_NAME => $groupName,
            static::GRP_AUTHOR => $userId,
            static::GRP_DESC => !empty($groupDesc) ? $groupDesc : $groupName,
        ];
        ksort($newGroup);
        $groupLines[] = $newGroup;

        # now save it
        $this->saveGroups($groupLines);

        $this->lock->delete();
    }

    /**
     * @return IGroup[]
     * @throws AuthException
     */
    public function readGroup(): array
    {
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $groupLines = $this->openGroups();
        $result = [];
        foreach ($groupLines as &$line) {
            $record = new FileGroup();
            $record->setData(
                intval($line[static::GRP_ID]),
                strval($line[static::GRP_NAME]),
                intval($line[static::GRP_AUTHOR]),
                strval($line[static::GRP_DESC])
            );
            $result[] = $record;
        }

        return $result;
    }

    public function updateGroup(IGroup $group): void
    {
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $this->lock->create();
        $groupLines = $this->openGroups();
        foreach ($groupLines as &$line) {
            if ($line[static::GRP_ID] == $group->getGroupId()) {
                // REFILL
                $line[static::GRP_NAME] = !empty($groupName) ? $groupName : $line[static::GRP_NAME] ;
                $line[static::GRP_DESC] = !empty($groupDesc) ? $groupDesc : $line[static::GRP_DESC] ;
            }
        }

        $this->saveGroups($groupLines);
        $this->lock->delete();
    }

    public function deleteGroup(int $groupId): void
    {
        if ($this->lock->has()) {
            throw new AuthException('Someone works with authentication. Please try again a bit later.');
        }

        $passLines = $this->openPassword();
        foreach ($passLines as &$line) {
            if ($line[static::PW_GROUP] == $groupId) {
                throw new AuthException('Group to removal still has members. Remove them first.');
            }
        }

        $changed = false;
        $this->lock->create();

        # update groups
        $openGroups = $this->openGroups();
        foreach ($openGroups as $index => &$line) {
            if ($line[static::GRP_ID] == $groupId) {
                unset($openGroups[$index]);
                $changed = true;
            }
        }

        # now save it
        if ($changed) {
            $this->saveGroups($openGroups);
        }
        $this->lock->delete();
    }

    /**
     * @return string[]
     */
    public function readClasses(): array
    {
        return [
            IAccessClasses::CLASS_MAINTAINER => 'Maintainer',
            IAccessClasses::CLASS_ADMIN => 'Admin',
            IAccessClasses::CLASS_USER => 'User',
        ];
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

    /**
     * @param string $input
     * @return string
     * @throws AuthException
     */
    protected function hashPassword(string $input): string
    {
        // older kwcms style
        return base64_encode(bin2hex($this->makeHash($this->passSalt($input))));
    }

    private function passSalt(string $input): string
    {
        $ln = strlen($input);
        # pass is too long and salt too short
        $salt = (strlen($this->salt) < ($ln*5))
            ? str_repeat($this->salt, 5)
            : $this->salt ;
        return substr($salt, $ln, $ln)
            . substr($input,0, (int)($ln/2))
            . substr($salt,$ln*2, $ln)
            . substr($input, (int)($ln/2))
            . substr($salt,$ln*3, $ln);
    }

    /**
     * @param string $word
     * @return string
     * @throws AuthException
     */
    private function makeHash(string $word): string
    {
        if (function_exists('mhash')) {
            return mhash(MHASH_SHA256, $word);
        }
        if (function_exists('hash')) {
            return hash('sha256', $word);
        }
        throw new AuthException('Cannot find function for making hashes!');
    }
}
