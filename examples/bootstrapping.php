<?php

//// Example bootstrap code for KWCMS - authentication

// ... part for autoloader

// where is the system?
$paths = new \kalanis\kw_paths\Path();
$paths->setDocumentRoot(realpath($_SERVER['DOCUMENT_ROOT']));
$paths->setPathToSystemRoot('/..');

// init config
\kalanis\kw_confs\Config::init(new \kalanis\kw_confs\Loaders\PhpLoader($paths));
\kalanis\kw_paths\Stored::init($paths);

// load virtual parts - if exists
$virtualDir = \kalanis\kw_confs\Config::get('Core', 'site.fake_dir', 'dir_from_config/');
$params = new \kalanis\kw_paths\Params\Request\Server();
$params->set($virtualDir)->process();
$paths->setData($params->getParams());

/// ...

// authorization tree
$authenticator = new \kalanis\kw_auth\Sources\Files\Volume\Files(
    new \kalanis\kw_auth\Mode\KwOrig(strval(\kalanis\kw_confs\Config::get('Admin', 'admin.salt'))),
    new \kalanis\kw_locks\Methods\FileLock(
        $paths->getDocumentRoot() . $paths->getPathToSystemRoot() . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . \kalanis\kw_locks\Interfaces\ILock::LOCK_FILE
    ),
    $paths->getDocumentRoot() . $paths->getPathToSystemRoot() . DIRECTORY_SEPARATOR . 'web'
);
\kalanis\kw_auth\Auth::setAuth($authenticator);
\kalanis\kw_auth\Auth::setGroups($authenticator);
\kalanis\kw_auth\Auth::setClasses($authenticator);
\kalanis\kw_auth\Auth::setAccounts($authenticator);

// session start here - when you have custom handler it must be loaded here!
//$storage = \kalanis\kw_storage\Helper::initCache();
//$storage->init(new Redis());
//$handler = new StorageSession($storage);
//session_set_save_handler($handler, true);
//session_start();

$session = new \kalanis\kw_input\Simplified\SessionAdapter(); // this one represents session info
$server = new \kalanis\kw_input\Simplified\ServerAdapter(); // this one represents server info

class ExtBanned extends \kalanis\kw_auth\Methods\Banned
{
    protected function getBanPath(): string
    {
        $path = \kalanis\kw_paths\Stored::getPath();
        return $path->getDocumentRoot() . $path->getPathToSystemRoot() . DIRECTORY_SEPARATOR . 'web';
    }
}

/// Auth itself
\kalanis\kw_auth\Auth::fill(
    new ExtBanned($authenticator,
        new \kalanis\kw_auth\Methods\UrlCerts($authenticator,
            new \kalanis\kw_auth\Methods\TimedSessions($authenticator,
                new \kalanis\kw_auth\Methods\CountedSessions($authenticator,
                    null,
                    $session,
                    100
                ),
                $session,
                $server
            ),
            new \kalanis\kw_address_handler\Handler(new \kalanis\kw_address_handler\Sources\ServerRequest())
        ),
        $paths,
        $server
    )
);
/// this one is that dummy for testing
//\kalanis\kw_auth\Auth::fill(
//    new \kalanis\kw_auth\Methods\Everytime(null, null)
//);


/// Then in authentication class...

abstract class AAuthenticate
{
    /** @var \kalanis\kw_auth\Interfaces\IUser|null */
    protected $user = null;
    /** @var \kalanis\kw_auth\AuthException|null */
    protected $error = null;

    public function process(\kalanis\kw_input\Interfaces\IFiltered $inputs): void
    {
        try {
            $sources = [\kalanis\kw_input\Interfaces\IEntry::SOURCE_EXTERNAL, \kalanis\kw_input\Interfaces\IEntry::SOURCE_CLI, \kalanis\kw_input\Interfaces\IEntry::SOURCE_POST, \kalanis\kw_input\Interfaces\IEntry::SOURCE_GET];
            $authTree = \kalanis\kw_auth\Auth::getTree();
            $authTree->findMethod($inputs->getInObject(null, $sources));
            if ($authTree->getMethod() && $authTree->getMethod()->isAuthorized()) {
                $this->user = $authTree->getMethod()->getLoggedUser();
                if (in_array($this->user->getClass(), $this->allowedAccessClasses())) {
                    $this->run();
                } else {
                    throw new \kalanis\kw_auth\AuthException('Restricted access', 405);
                }
            }
        } catch (\kalanis\kw_auth\AuthException | \kalanis\kw_locks\LockException $ex) {
            $this->error = $ex;
        }
    }

    /**
     * Process things under authentication
     */
    abstract protected function run(): void;

    /**
     * Which users can do anything in that module?
     * @see \kalanis\kw_auth\Interfaces\IAccessClasses
     * @return int[]
     */
    abstract protected function allowedAccessClasses(): array;

    public function output(): string
    {
        if ($this->user) {
            return $this->result();
        } elseif ($this->error) {
            throw $this->error;
        } else {
            throw new \kalanis\kw_auth\AuthException('Authorize first', 401);
        }
    }

    /**
     * What will be answered
     * @return string
     */
    abstract protected function result(): string;
}

