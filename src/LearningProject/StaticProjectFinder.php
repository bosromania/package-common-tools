<?php

namespace Bosromania\PackageCommonTools\LearningProject;

use Arshwell\Monolith\Folder;

use Bosromania\PackageCommonTools\LearningProject\Project;

/**
 * Static class for finding BOS Projects in a certain location folder.
 */
final class StaticProjectFinder
{
    /**
     * @return Project[]
     */
    static function findIn(string $location, string $webdevUserClass, array $urlPathRegex): array
    {
        $projects = array();

        $folders = Folder::children($location);

        foreach ($folders as $folder) {
            if (self::isLearningProject($folder)) {
                $learningProject = new Project($folder, $webdevUserClass, $urlPathRegex);

                if ($learningProject->isWorkProject()) {
                    if ($learningProject->isDevProject()) {
                        $projects[$learningProject->getProjectGeneration()]['dev'][$learningProject->getFolderAuthor()] = $learningProject;
                    }
                    else {
                        $projects[$learningProject->getProjectGeneration()]['main'] = $learningProject;
                    }
                }
            }
            else {
                foreach (glob($folder, GLOB_ONLYDIR) as $path) {
                    $projects = array_merge_recursive($projects, self::findIn($path, $webdevUserClass, $urlPathRegex));
                }
            }
        }

        return $projects;
    }

    /**
     * Check by different files, to be sure it's about a learning project.
     *
     * Note: projects from 21-23 generation don't have a pattern.
     * That's should be different conditions.
     */
    private static function isLearningProject(string $folder): bool
    {
        return (glob("$folder/*.html") || glob("$folder/*.php"));
    }
}
