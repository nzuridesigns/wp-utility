<?php

namespace Jcodify\CarRentalTheme\Wordpress;

use Jcodify\CarRentalTheme\IconManager;

class ThemeIconManager
{
    private static ?IconManager $instance = null;

    /**
     * Get the instance of the IconManager.
     *
     * @return IconManager
     */
    public static function getInstance(): IconManager
    {
        if (self::$instance === null) {
            self::$instance = new IconManager(get_template_directory() . '/resources/svg');
            self::$instance->setDefaultAttributes([
                'class' => 'icon-class',
                'width' => '24',
                'height' => '24',
            ]);
        }

        return self::$instance;
    }
}
