<?php

return [

    'admin'     => [
        'prefix'            => env('CMS_ADMIN_PREFIX', 'admin'),
        'version'           => env('CMS_ADMIN_VERSION', 'v1'),
        'per_page'          => env('CMS_ADMIN_PER_PAGE',25),
        'base_route_name'   => env('CMS_ADMIN_BASE_ROUTE_NAME', 'admin')
    ]

];
