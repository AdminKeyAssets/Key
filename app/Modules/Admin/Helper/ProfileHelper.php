<?php


namespace App\Modules\Admin\Helper;


class ProfileHelper
{

    /**
     * @param string $baseRouteName
     * @param string $baseModuleName
     * @return array
     */
    public static function getRoutes($baseRouteName = 'admin', $baseModuleName = 'profile')
    {

        $baseName = $baseRouteName . '.' . $baseModuleName . '.';

        return [
            'create_form_data'  => route($baseName . 'form_data'),
            'save'      => route($baseName . 'save'),
        ];
    }

    /**
     * @param string $baseLangName
     * @param string $baseModuleName
     * @return array
     */
    public static function getLang($baseLangName = 'admin', $baseModuleName = 'profile')
    {

        $baseFullLangName = $baseLangName . '.' . $baseModuleName . '.';

        return [

            'menu'                      => __('Menu'),
            'update'                    => __('Update'),
            'update_successfully'       => __('Updated Successfully'),
            'save_successfully'         => __('Saved'),
            'delete_successfully'       => __('Deleted'),
            'save_text'                 => __('Save'),

            //Inputs
            'name'                              => __('Name'),
            'email'                             => __('Email'),
            'password'                          => __('Password'),
            'generate_password'                 => __('Generate Password'),
            'confirm_save'                      => __('Confirm Save'),
            'confirm_save_yes'                  => __('Yes'),
            'confirm_save_no'                   => __('No'),
            'created_at'                        => __('Created at'),


        ];

    }

}
