<?php


namespace App\Modules\Admin\Helper;


class UserHelper
{

    /**
     * @param string $baseRouteName
     * @param string $baseModuleName
     * @return array
     */
    public static function getRoutes($baseRouteName = 'admin', $baseModuleName = 'user')
    {

        $baseName = $baseRouteName . '.' . $baseModuleName . '.';

        return [
            'create_form_data'  => route($baseName . 'create_form_data'),
            'create'    => route($baseName . 'create_form'),
            'save'      => route($baseName . 'save'),
            'delete'    => route($baseName . 'delete')
        ];
    }

    /**
     * @param string $baseLangName
     * @param string $baseModuleName
     * @return array
     */
    public static function getLang($baseLangName = 'admin', $baseModuleName = 'user')
    {

        $baseFullLangName = $baseLangName . '.' . $baseModuleName . '.';

        return [

            'menu'                      => __('Menu'),
            'index'                     => __('Index'),
            'create'                    => __('Create'),
            'actions'                   => __('Actions'),
            'delete_title'              => __('Delete'),
            'update_successfully'       => __('Update Successfully'),
            'save_successfully'         => __('Save Successfully'),
            'delete_successfully'       => __('Delete Successfully'),
            'save_text'                 => __('Save'),

            //Inputs
            'name'                              => __('Name'),
            'email'                             => __('Email'),
            'password'                          => __('Password'),
            'generate_password'                 => __('Generate Password'),
            'select_role_placeholder'           => __('Select Role'),
            'roles'                             => __('Roles'),
            'select_roles'                      => __('Select Roles'),
            'selected_roles'                    => __('Selected Roles'),
            'confirm_save'                      => __('Confirm Save'),
            'confirm_save_yes'                  => __('Yes'),
            'confirm_save_no'                   => __('No'),
            'created_at'                        => __('Created At'),
            'roles_name'                        => __('Roles Name'),


        ];

    }

}
