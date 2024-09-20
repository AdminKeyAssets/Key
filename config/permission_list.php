<?php

$default_permissions = [

    'index' => [
        'key' => '{module_name}_list',
        'label' => '{module_name} list'
    ],
    'create' => [
        'key' => '{module_name}_create',
        'label' => '{module_name} create'
    ],
    'update' => [
        'key' => '{module_name}_update',
        'label' => '{module_name} update'
    ],
    'delete' => [
        'key' => '{module_name}_delete',
        'label' => '{module_name} delete'
    ],
    'view' => [
        'key' => '{module_name}_view',
        'label' => '{module_name} view'
    ]

];

$custom_permissions = [
    'export' => [
        'key' => '{module_name}_export',
        'label' => '{module_name} export'
    ]
];

return [
    /**
     * User module permissions.
     */
    'user' => [
        'default' => $default_permissions,
        'custom' => $custom_permissions
    ],

    /**
     * Roles module permissions.
     */
    'role' => [
        'default' => $default_permissions
    ],

    'asset' => [
        'default' => $default_permissions
    ],
    'payment' => [
        'default' => $default_permissions
    ],
    'comment' => [
        'default' => $default_permissions
    ],
    'rental' => [
        'default' => $default_permissions
    ],
    'investor' => [
        'default' => $default_permissions,
        'custom' => $custom_permissions
    ],
    'investment' => [
        'default' => $default_permissions
    ],
    'lead' => [
        'default' => $default_permissions,
        'custom' => $custom_permissions
    ],
    'sale' => [
        'default' => $default_permissions,
        'custom' => $custom_permissions
    ],
    'template' => [
        'default' => $default_permissions,
    ],
];
