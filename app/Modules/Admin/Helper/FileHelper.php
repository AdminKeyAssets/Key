<?php


namespace App\Modules\Admin\Helper;


class FileHelper
{

    /**
     * @param string $baseRouteName
     * @param string $baseModuleName
     * @return array
     */
    public static function getRoutes($baseRouteName = 'admin', $baseModuleName = 'file')
    {

        $baseName = $baseRouteName . '.' . $baseModuleName . '.';

        return [
            'upload'    => route($baseName . 'upload'),
        ];
    }

    /**
     * @param string $baseLangName
     * @param string $baseModuleName
     * @return array
     */
    public static function getLang($baseLangName = 'admin', $baseModuleName = 'file')
    {

        $baseFullLangName = $baseLangName . '.' . $baseModuleName . '.';

        return [
            'upload_successfully'         => __('Uploaded Successfully'),
        ];

    }

}
