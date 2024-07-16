<?php

return [

    /**
     * Handcrafted member.
     */
    'handcrafted_by'        => '',

    'version'               => 'v.1',

    /**
     * Handcrafted member url.
     */
    'handcrafted_by_url'        => '',

    /**
     * Admin user avatar url.
     */
    'user_avatar'           => 'admin_resources/img/placeholders/avatars/avatar.jpg',

    /**
     * Project name.
     */
    'project_name'          => env('PROJECT_NAME', 'CRM'),

    /**
     * Project avatar url.
     */
    'project_avatar'        => 'admin_resources/img/placeholders/avatars/avatar2.jpg',

    /**
     * Login page logo.
     */
    'login_logo'                => [
        [
            'src'       => 'admin_resources/img/placeholders/avatars/KA_WHITE-01.svg',
            'style'     => 'width: 100px'
        ]
    ],


    /**
     * Recaptcha config.
     */
    'recaptcha'             => [
        'modules'     => [
            'login' => [
                'status'    => env('RECAPTCHA_MODULE_LOGIN_STATUS', true)
            ]
        ],
        'secret_key'    => env('RECAPTCHA_SECRET_KEY','6Ld9P7MZAAAAABVFd6wxIZq25YHwRQqX1xPCmeG8'),
        'public_key'    => env('RECAPTCHA_PUBLIC_KEY', '6Ld9P7MZAAAAABTzd1xQY2XkL8H6wT7nKxjb6tGN')
    ],

    'login_web_modules'     => [],

    /**
     * Admin user
     */
    'admin_user_name'           => env('ADMIN_USER_EMAIL', ''),
    'admin_user_password'       => env('ADMIN_USER_PASSWORD', ''),

    /**
     * Image upload config.
     */
    'image'         => [

        /**
         * Enable or disable upload resolutions.
         */
        'upload_resolutions'    => env('UPLOAD_IMAGE_RESOLUTIONS', false),

        /**
         * Resolution list.
         */
        'resolutions'   => [
            600,
            1200,
            1800
        ]

    ]


];
