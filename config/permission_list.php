<?php

$default_permissions = [

    'index'       => [
        'key' => '{module_name}_index',
        'label' => '{module_name} index'
    ],
    'create'       => [
        'key' => '{module_name}_create',
        'label' => '{module_name} create'
    ],
    'update'       => [
        'key' => '{module_name}_update',
        'label' => '{module_name} update'
    ],
    'delete'       => [
        'key' => '{module_name}_delete',
        'label' => '{module_name} delete'
    ]

];

return [
    /**
     * User module permissions.
     */
    'user'     => [
        'default'   => $default_permissions
    ],

    /**
     * Roles module permissions.
     */
    'role'     => [
        'default'   => $default_permissions
    ],

    'asset'     => [
        'default'   => $default_permissions
    ],
];
