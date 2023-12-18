<?php

namespace Bosromania\PackageCommonTools;

use Arshwell\Monolith\Table\Files\Image;
use Bosromania\PackageCommonTools\BosApp\App;

final class HTML
{
    static function headerLogo (?Image $logoFile, ?string $logoText, App $bosApp, bool $backdropFilter = false): string
    {
        ob_start();

        if ($logoFile) {
            $logoFile = $logoFile->url('small');
        }
        if (empty($logoText)) {
            $logoText = 'Logo text not found';
        }

        $containerClasses = call_user_func(function () use ($backdropFilter) {
            $classes = '';

            if (is_bool($backdropFilter) && $backdropFilter == true) {
                $classes = 'backdrop-filter';
            }

            return $classes;
        });
        ?>

        <style type="text/css">
            .bosromania--package-common-tools--header-logo {
                display: block;
            }
            .bosromania--package-common-tools--header-logo .header-logo--container {
                display: inline-block;
                white-space: nowrap;
                overflow: hidden;
            }
            .bosromania--package-common-tools--header-logo .header-logo--container.backdrop-filter {
                padding: 5px;
                border-radius: 5px;
                -webkit-border-radius: 5px;
                -o-border-radius: 5px;
                -moz-border-radius: 5px;
                backdrop-filter: contrast(0.1);
            }
            .bosromania--package-common-tools--header-logo .header-logo--container .header-logo--image.type--dev {
                max-width: calc(100% - 85px);
            }
            .bosromania--package-common-tools--header-logo .header-logo--container .header-logo--image.type--stage {
                max-width: calc(100% - 65px);
            }
            .bosromania--package-common-tools--header-logo .header-logo--container .header-logo--bos-app-badge {
                cursor: help;
                position: relative;
                top: -3px;
                color: #fff;
                background-color: #dc3545;
                display: inline-flex;
                padding: 5px 8px;
                line-height: 20px;
                text-align: center;
                white-space: nowrap;
                vertical-align: baseline;
                -webkit-text-fill-color: initial;
                border-radius: 0.25rem;
                -webkit-border-radius: 0.25rem;
                -o-border-radius: 0.25rem;
                -moz-border-radius: 0.25rem;
            }
            .bosromania--package-common-tools--header-logo .header-logo--container .header-logo--bos-app-badge .header-logo--webdev-user-avatar {
                height: 20px;
                padding: 1px;
                background-color: #fff;
                border: 1px solid #dee2e6;
            }
        </style>

        <div class="bosromania--package-common-tools--header-logo">
            <div class="header-logo--container <?= $containerClasses ?>">
                <?php
                if ($logoFile) { ?>
                    <img class="header-logo--image type--<?= $bosApp->getAppType() ?>"
                    src="<?= $logoFile ?>" alt="<?= $logoText ?>" />
                <?php }
                else {
                    echo $logoText;
                } ?>

                <?php
                if ($bosApp->isWorkApp()) { ?>
                    <span class="header-logo--bos-app-badge" title="You are in a Work version">
                        <?= ucfirst($bosApp->getAppType()) ?>

                        <?php
                        if ($bosApp->isDevApp()) {
                            if ($bosApp->getWebDevUser()) { ?>
                                <img src="<?= $bosApp->getWebDevUser()->file('avatar')->url('tinny') ?>"
                                class="header-logo--webdev-user-avatar rounded-circle ml-1"
                                alt="<?= $bosApp->getWebDevUser()->g_name ?>"
                                title="<?= $bosApp->getWebDevUser()->g_name ?>">
                            <?php }
                            else { ?>
                                <img src=""
                                class="header-logo--webdev-user-avatar rounded-circle ml-1"
                                alt="No WebDev user"
                                title="User not found">
                            <?php }
                        } ?>
                    </span>
                <?php } ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}
