<?php
// Messaging Center plugin configuration
return [
    'MessagingCenter' => [
        'systemUser' => [
            'id' => '00000000-0000-0000-0000-000000000000',
            'name' => 'SYSTEM'
        ],
        'typeahead' => [
            'min_length' => 1,
            'timeout' => 300
        ],
        'api' => [
            'token' => null
        ],
        'welcomeMessage' => [
            'enabled' => 1,
            'subject' => '',
            'projectName' => 'Qobrix',
        ]
    ]
];
