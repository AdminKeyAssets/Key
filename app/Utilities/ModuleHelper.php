<?php

namespace App\Utilities;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ModuleHelper
{

    /**
     * @param $slug
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function isEnabled($slug)
    {

        try {

            /**
             * @var $allModules Collection
             */
            $allModules = self::allModules();

        } catch (\Exception $ex) {
            return false;
        }

        return $allModules->where('slug', $slug)->where('enabled', true)->count() > 0;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function allModules()
    {
        $cachePath = self::getCachePath();

        $files = new Filesystem();

        return collect(json_decode($files->get($cachePath), true));
    }

    /**
     * @return string
     */
    private static function getCachePath()
    {

        $files = new Filesystem();

        if (!$files->isDirectory(storage_path('app/modules'))) {
            $files->makeDirectory(storage_path('app/modules'));
        }
        return storage_path("app/modules/app.json");
    }

}
