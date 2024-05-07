<?php


return [

    'types'     => [
        'text',
        'textarea',
        'integer',
        'select'
    ],

    'attributes'    => [

        'client_paginate_limit'     => [
            'key'           => 'client_paginate_limit',
            'type'          => 'integer',
            'label'         => 'config.client_paginate_limit_label',
            'value'         => 25
        ],
        'admin_paginate_limit'     => [
            'key'           => 'admin_paginate_limit',
            'type'          => 'integer',
            'label'         => 'config.admin_paginate_limit_label',
            'value'         => 25
        ],
        'client_order_by_column'     => [
            'key'           => 'client_order_by_column',
            'type'          => 'select',
            'label'         => 'config.client_order_by_column_label',
            'value'         => 'created_at',
            'options'       => [
                [
                    'value'         => 'created_at',
                    'label'         => 'config.client_order_by_column_created_at',
                ],
                [
                    'value'         => 'updated_at',
                    'label'         => 'config.client_order_by_column_updated_at',
                ]
            ]
        ],
        'client_order_by_direction'     => [
            'key'           => 'client_order_by_direction',
            'type'          => 'select',
            'label'         => 'config.client_order_by_direction_label',
            'value'         => 'desc',
            'options'       => [
                [
                    'value'         => 'asc',
                    'label'         => 'config.client_order_by_direction_asc'
                ],
                [
                    'value'         => 'desc',
                    'label'         => 'config.client_order_by_direction_desc'
                ]
            ]
        ],
        'admin_order_by_column'     => [
            'key'           => 'admin_order_by_column',
            'type'          => 'select',
            'label'         => 'config.admin_order_by_column_label',
            'value'         => 'created_at',
            'options'       => [
                [
                    'value'         => 'created_at',
                    'label'         => 'config.admin_order_by_column_created_at'
                ],
                [
                    'value'         => 'updated_at',
                    'label'         => 'config.admin_order_by_column_updated_at'
                ]
            ]
        ],

        'admin_order_by_direction'     => [
            'key'           => 'admin_order_by_direction',
            'type'          => 'select',
            'label'         => 'config.admin_order_by_direction_label',
            'value'         => 'desc',
            'options'       => [
                [
                    'value'         => 'asc',
                    'label'         => 'config.admin_order_by_direction_asc'
                ],
                [
                    'value'         => 'desc',
                    'label'         => 'config.admin_order_by_direction_desc'
                ]
            ]
        ]

    ]

];
