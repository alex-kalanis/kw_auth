<?php

//// Example bootstrap code for KWCMS - authentication

// ... part for autoloader

// where is the system?
$paths = new \kalanis\kw_paths\Path();
$paths->setDocumentRoot(realpath($_SERVER['DOCUMENT_ROOT']));
$paths->setPathToSystemRoot('/..');

// init config
\kalanis\kw_confs\Config::init($paths);

// load virtual parts - if exists
$virtualDir = \kalanis\kw_confs\Config::get('Core', 'site.fake_dir', 'dir_from_config/');
$params = new \kalanis\kw_paths\Params\Request\Server();
$params->set($virtualDir)->process();
$paths->setData($params->getParams());

/// ...

// authorization tree
$authenticator = new \kalanis\kw_auth\Sources\Files(
    $paths->getDocumentRoot() . $paths->getPathToSystemRoot() . DIRECTORY_SEPARATOR . 'web',
    strval(\kalanis\kw_confs\Config::get('Admin', 'admin.salt'))
);
$session = new ArrayObject(); // this one represents session info

class ExBanned extends \kalanis\kw_auth\Methods\Banned
{
    protected function getBanPath(): string
    {
        $path = \kalanis\kw_confs\Config::getPath();
        return $path->getDocumentRoot() . $path->getPathToSystemRoot() . DIRECTORY_SEPARATOR . 'web';
    }
}

/// Auth itself
\kalanis\kw_auth\Auth::init(
    new ExBanned($authenticator,
        new \kalanis\kw_auth\Methods\Certs($authenticator,
            new \kalanis\kw_auth\Methods\TimedSessions($authenticator,
                new \kalanis\kw_auth\Methods\CountedSessions($authenticator,
                    null,
                    $session,
                    100
                ),
                $session
            ),
            new \kalanis\kw_address_handler\Handler(new \kalanis\kw_address_handler\Sources\ServerRequest())
        )
    )
);
/// this one is that dummy for testing
//\kalanis\kw_auth\Auth::init(
//    new \kalanis\kw_auth\Methods\Everytime(null, null)
//);


/// Then in authentication class...

abstract class AAuthenticate
{
    /** @var \kalanis\kw_auth\Interfaces\IUser|null */
    protected $user = null;
    /** @var \kalanis\kw_auth\AuthException|null */
    protected $error = null;

    public function process(\kalanis\kw_input\IVariables $inputs): void
    {
        try {
            $sources = [\kalanis\kw_input\Interfaces\IEntry::SOURCE_EXTERNAL, \kalanis\kw_input\Interfaces\IEntry::SOURCE_CLI, \kalanis\kw_input\Interfaces\IEntry::SOURCE_POST, \kalanis\kw_input\Interfaces\IEntry::SOURCE_GET];
            \kalanis\kw_auth\Auth::findMethod($inputs->getInObject(null, $sources));
            if (\kalanis\kw_auth\Auth::getMethod() && \kalanis\kw_auth\Auth::getMethod()->isAuthorized()) {
                $this->user = \kalanis\kw_auth\Auth::getMethod()->getLoggedUser();
                if (in_array($this->user->getClass(), $this->allowedAccessClasses())) {
                    $this->run();
                } else {
                    throw new \kalanis\kw_auth\AuthException('Restricted access', 405);
                }
            }
        } catch (\kalanis\kw_auth\AuthException $ex) {
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

