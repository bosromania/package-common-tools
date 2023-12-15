<?php

namespace Bosromania\PackageCommonTools\BosApp;

use Arshwell\Monolith\Folder;
use Arshwell\Monolith\File;

use Bosromania\PackageCommonTools\BosApp\App;

/**
 * Static class for finding BOS Apps in a certain location folder.
 */
final class StaticAppFinder
{
    /**
     * @return App[]
     */
    static function findIn(string $location, array $urlPathRegex, string $webdevUserClass, array $appLogoClasses = null): array
    {
        $apps = array();

        $folders = Folder::children($location);

        foreach ($folders as $folder) {
            if (self::isBosApp($folder)) {
                $bosApp = new App($folder, $urlPathRegex, $webdevUserClass, $appLogoClasses);

                if ($bosApp->isWorkApp()) {
                    if ($bosApp->isDevApp()) {
                        $apps[File::name($folder)]['dev'][] = $bosApp;
                    }
                    else {
                        $apps[File::name($folder)]['stage'] = $bosApp;
                    }
                }
            }
            else {
                foreach (glob($folder, GLOB_ONLYDIR) as $path) {
                    $apps = array_merge_recursive($apps, self::findIn($path, $urlPathRegex, $webdevUserClass, $appLogoClasses));
                }
            }
        }

        return $apps;
    }

    /**
     * Check by different folders/files, to be sure it's about a BOS app.
     */
    private static function isBosApp(string $folder): bool
    {
        return (
            is_file("$folder/index.php") &&
            is_file("$folder/composer.json") &&
            is_dir("$folder/config")
        );
    }
}
