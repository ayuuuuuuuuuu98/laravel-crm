<?php

return [
    [
        'key' => 'settings.workspace',
        'name' => 'scalvion-foundation::app.menu.workspace',
        'route' => 'admin.scalvion.notifications.history',
        'sort' => 4,
        'icon-class' => '',
    ],
    [
        'key' => 'settings.workspace.notifications',
        'name' => 'scalvion-foundation::app.menu.notifications',
        'route' => 'admin.scalvion.notifications.history',
        'sort' => 1,
        'icon-class' => 'icon-mail',
    ],
    [
        'key' => 'settings.workspace.preferences',
        'name' => 'scalvion-foundation::app.menu.preferences',
        'route' => 'admin.scalvion.notifications.preferences',
        'sort' => 2,
        'icon-class' => 'icon-setting',
    ],
    [
        'key' => 'settings.workspace.audit_logs',
        'name' => 'scalvion-foundation::app.menu.audit_logs',
        'route' => 'admin.scalvion.audit-logs.index',
        'sort' => 3,
        'icon-class' => 'icon-activity',
    ],
];
