<?php

namespace Bosromania\PackageCommonTools\LearningProject;

use Arshwell\Monolith\Folder;
use Arshwell\Monolith\File;
use Arshwell\Monolith\URL;

final class Project
{
    private $projectPath;
    private $projectLink = null;
    private $projectName;

    /** @var string */
    private $folderAuthor = null;

    /** @var string */
    private $projectGeneration = null;

    /** @var null|string */
    private $faviconSrc = null;

    /** @var \DateTime|null */
    private $lastDeployAt = null;

    /** @var bool */
    private $isStageProject = false;

    /** @var bool */
    private $isDevProject = false;

    /** @var User|null */
    private $webDevUser = null;


    function __construct(string $projectPath, string $webdevUserClass, array $urlPathRegex)
    {
        $this->projectPath = $projectPath;
        $this->projectName = File::name($projectPath);
        $this->lastDeployAt = $this->fetchLastDeployAt($projectPath);

        $this->isStageProject = preg_match($urlPathRegex['stage'], $this->projectPath, $matches);

        if ($this->isStageProject()) {
            $this->faviconSrc = $this->saveFaviconSrc($projectPath);
        }

        if ($matches) {
            $urlPath = $matches[0];
            $this->projectGeneration = $matches['gen'];
        }

        $this->isDevProject = preg_match($urlPathRegex['dev'], $this->projectPath, $matches);

        if ($matches) {
            $urlPath = $matches[0];
            $this->folderAuthor = $matches['author'];
            $this->projectGeneration = $matches['gen'];
        }

        // set link if project is on the same subdomain
        if (strpos($projectPath, Folder::root()) === 0 && !empty($urlPath)) {
            $this->projectLink = URL::protocol() .'://'. ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']) .'/'. $urlPath;
        }

        // get webDevUser
        if ($this->isDevProject) {
            $this->webDevUser = ($webdevUserClass)::first(
                array(
                    'columns'   => "g_name, g_avatar_color",
                    'where'     => "g_email LIKE ?",
                    'files'     => true
                ),
                array($matches[1] . '@%')
            );
        }
    }


    public function getFolderAuthor(): ?string
    {
        return $this->folderAuthor;
    }

    public function getProjectGeneration(): string
    {
        return $this->projectGeneration;
    }

    public function getFaviconSrc(): ?string
    {
        return $this->faviconSrc;
    }

    /**
     * If returns false, it's a Dev project.
     */
    public function isStageProject(): bool
    {
        return $this->isStageProject;
    }

    /**
     * If returns false, it's a Stage project.
     */
    public function isDevProject(): bool
    {
        return $this->isDevProject;
    }

    /**
     * If returns false, it's probably a Live project.
     */
    public function isWorkProject(): bool
    {
        return $this->isStageProject || $this->isDevProject;
    }

    /**
     * If returns 'undefined', it's probably a Live project.
     *
     * @return string stage/dev/undefined
     */
    public function getProjectType(): string
    {
        if ($this->isStageProject) {
            return 'stage';
        }
        if ($this->isDevProject) {
            return 'dev';
        }

        return 'undefined';
    }

    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    public function getProjectLink(): ?string
    {
        return $this->projectLink;
    }

    public function getProjectName(): string
    {
        return $this->projectName;
    }

    public function getLastDeployAt(): ?\DateTime
    {
        return $this->lastDeployAt;
    }

    public function getWebDevUser(): ?object
    {
        return $this->webDevUser;
    }


    private function fetchLastDeployAt(string $folder): ?\DateTime
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

    private function saveFaviconSrc(string $folder): ?string
    {
        $faviconSrc = "$folder/favicon.ico";

        if (!is_file($faviconSrc)) {
            return null;
        }

        // Read image path, convert to base64 encoding
        $imageData = base64_encode(file_get_contents($faviconSrc));

        // Format the image SRC:  data:{mime};base64,{data};
        $src = 'data: '.mime_content_type($faviconSrc).';base64,'.$imageData;

        return $src;
    }
}
