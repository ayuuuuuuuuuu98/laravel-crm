<?php

return [
    [
        'key' => 'whatsapp',
        'name' => 'whatsapp::app.menu.title',
        'route' => 'admin.whatsapp.inbox.index',
        'sort' => 9,
        'icon-class' => 'icon-mail',
    ],
    [
        'key' => 'whatsapp.inbox',
        'name' => 'whatsapp::app.menu.inbox',
        'route' => 'admin.whatsapp.inbox.index',
        'sort' => 1,
        'icon-class' => 'icon-mail',
    ],
    [
        'key' => 'whatsapp.settings',
        'name' => 'whatsapp::app.menu.settings',
        'route' => 'admin.whatsapp.settings.index',
        'sort' => 2,
        'icon-class' => 'icon-phone',
    ],
];
