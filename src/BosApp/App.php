<?php

namespace Bosromania\PackageCommonTools\BosApp;

use Arshwell\Monolith\Folder;
use Arshwell\Monolith\File;
use Arshwell\Monolith\URL;
use Arshwell\Monolith\Table\Files\Image;

use Bosromania\PackageCommonTools\Table\AnotherPlatform\WebDev\Auth\User;

final class App
{
    /** @var string */
    private $appPath;

    /** @var string */
    private $appLink;

    /** @var string */
    private $appName;

    /** @var string */
    private $folderAuthor = null;

    /** @var \DateTime|null */
    private $lastDeployAt = null;

    /** @var bool */
    private $isThisApp = false;

    /** @var bool */
    private $isStageApp = false;

    /** @var bool */
    private $isDevApp = false;

    /** @var User|null */
    private $webDevUser = null;

    /** @var Image|null */
    private $appLogoEmblemFile = null;

    /** @var Image|null */
    private $appLogoFaviconFile = null;


    function __construct(string $appPath, array $urlPathRegex, string $webdevUserClass, array $appLogoClasses = null)
    {
        $this->appPath = $appPath;
        $this->appName = File::name($appPath);
        $this->lastDeployAt = $this->fetchLastDeployAt($appPath);

        if (rtrim(Folder::root(), '/') == rtrim($this->appPath, '/')) {
            $this->isThisApp = true;
        }

        $this->isStageApp = preg_match($urlPathRegex['stage'], $this->appPath, $matches);

        if ($matches) {
            $urlPath = $matches[0];
        }

        $this->isDevApp = preg_match($urlPathRegex['dev'], $this->appPath, $matches);

        if ($matches) {
            $urlPath = $matches[0];
            $this->folderAuthor = $matches['author'];
        }

        if (!empty($urlPath)) {
            $this->appLink = URL::protocol() .'://'. ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']) .'/'. $urlPath;
        }

        // get webDevUser
        if ($this->isDevApp) {
            $this->webDevUser = ($webdevUserClass)::first(
                array(
                    'columns'   => "g_name",
                    'where'     => "g_email LIKE ?",
                    'files'     => true,
                ),
                array($matches[1] . '@%')
            );
        }

        $appLogoFiles = null;

        if ($appLogoClasses) {
            // get logoFiles
            if (!empty($appLogoClasses[$this->appName])) {
                $appLogoFiles = ($appLogoClasses[$this->appName]['class'])::first(array(
                    'columns' => 'id_logo',
                    'where' => 'visible = 1',
                    'files' => true,
                    'fileStorageKey' => $appLogoClasses[$this->appName]['fileStorageKey']
                ));
            }
        }

        if ($appLogoFiles) {
            // set logoEmblemFile
            if ($appLogoFiles->file('header_fixed') && $appLogoFiles->file('header_fixed')->urls()) {
                $this->appLogoEmblemFile = $appLogoFiles->file('header_fixed');
            }
            else if ($appLogoFiles->file('header') && $appLogoFiles->file('header')->urls()) {
                $this->appLogoEmblemFile = $appLogoFiles->file('header');
            }

            // set logoFaviconFile
            if ($appLogoFiles->file('favicon_site') && $appLogoFiles->file('favicon_site')->urls()) {
                $this->appLogoFaviconFile = $appLogoFiles->file('favicon_site');
            }
        }
    }

    public function getFolderAuthor(): ?string
    {
        return $this->folderAuthor;
    }

    /**
     * If returns false, it's a Dev or Live app.
     */
    public function isStageApp(): bool
    {
        return $this->isStageApp;
    }

    /**
     * If returns false, it's a Stage or Live app.
     */
    public function isDevApp(): bool
    {
        return $this->isDevApp;
    }

    /**
     * If returns false, it's probably a Live app.
     */
    public function isWorkApp(): bool
    {
        return $this->isStageApp || $this->isDevApp;
    }

    /**
     * If returns 'undefined', it's probably a Live app.
     *
     * @return string stage/dev/undefined
     */
    public function getAppType(): string
    {
        if ($this->isStageApp) {
            return 'stage';
        }
        if ($this->isDevApp) {
            return 'dev';
        }

        return 'undefined';
    }

    public function getAppPath(): string
    {
        return $this->appPath;
    }

    public function getAppLink(): string
    {
        return $this->appLink;
    }

    public function getAppName(): string
    {
        return $this->appName;
    }

    public function getLastDeployAt(): ?\DateTime
    {
        return $this->lastDeployAt;
    }

    public function isThisApp(): bool
    {
        return $this->isThisApp;
    }

    public function getWebDevUser(): ?object
    {
        return $this->webDevUser;
    }

    public function getAppLogoEmblemFile(): ?Image
    {
        return $this->appLogoEmblemFile;
    }

    public function getAppLogoFaviconFile(): ?Image
    {
        return $this->appLogoFaviconFile;
    }


    private static function fetchLastDeployAt(string $folder): ?\DateTime
    {
        $stateFile = "$folder/.ftp-deploy-sync-state.json";

        if (!is_file($stateFile)) {
            return null;
        }

        // check if the file was corrupted
        if (json_decode(file_get_contents($stateFile), true) == null) {
            return null;
        }

        $lastDeployAt = new \DateTime();

        $lastDeployAt->setTimestamp(filemtime($stateFile));

        return $lastDeployAt;
    }
}
