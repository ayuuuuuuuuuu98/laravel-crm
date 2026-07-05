<?php

return [
    [
        'key' => 'whatsapp',
        'name' => 'whatsapp::app.acl.whatsapp',
        'route' => 'admin.whatsapp.inbox.index',
        'sort' => 1,
    ],
    [
        'key' => 'whatsapp.inbox',
        'name' => 'whatsapp::app.acl.inbox',
        'route' => 'admin.whatsapp.inbox.index',
        'sort' => 2,
    ],
    [
        'key' => 'whatsapp.view',
        'name' => 'whatsapp::app.acl.view',
        'route' => 'admin.whatsapp.inbox.index',
        'sort' => 3,
    ],
    [
        'key' => 'settings.other_settings.whatsapp',
        'name' => 'whatsapp::app.acl.settings',
        'route' => ['admin.whatsapp.settings.index', 'admin.whatsapp.settings.store'],
        'sort' => 4,
    ],
];
