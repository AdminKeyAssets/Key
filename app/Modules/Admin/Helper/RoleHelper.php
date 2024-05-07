<?php


namespace App\Modules\Admin\Helper;


class RoleHelper
{

    /**
     * @param string $baseRouteName
     * @param string $baseModuleName
     * @return array
     */
    public static function getRoutes($baseRouteName = 'admin', $baseModuleName = 'role')
    {

        $baseName = $baseRouteName . '.' . $baseModuleName . '.';

        return [
            'create_form_data' => route($baseName . 'create_form_data'),
            'create' => route($baseName . 'create_form'),
            'save' => route($baseName . 'save'),
            'delete' => route($baseName . 'delete')
        ];
    }

    /**
     * @param string $baseLangName
     * @param string $baseModuleName
     * @return array
     */
    public static function getLang($baseLangName = 'admin', $baseModuleName = 'role')
    {

        $baseFullLangName = $baseLangName . '.' . $baseModuleName . '.';

        return [

            'menu' => __('Menu'),
            'index' => __('Index'),
            'create' => __('Create'),
            'actions' => __('Actions'),
            'delete_title' => __('Delete'),
            'update_successfully' => __('Update Successfully'),
            'save_successfully' => __('Save Successfully'),
            'delete_successfully' => __('Delete Successfully'),
            'save_text' => __('Save'),

            //Inputs
            'name' => __('Name'),
            'email' => __('Email'),
            'confirm_save' => __('Save'),
            'confirm_save_yes' => __('Yes'),
            'confirm_save_no' => __('No'),
            'created_at' => __('Created At'),
            'roles_name' => __('Roles Name'),
            'permissions' => __('Permissions'),
            'select_permissions' => __('Select Permissions'),
            'selected_permissions' => __('Selected Permissions'),
            'select_permission_placeholder' => __('Select Permissions'),
            'permissions_name' => __('Permission Name'),
        ];

    }

}
