<?php
// Messaging Center plugin configuration
return [
    'MessagingCenter' => [
        'systemUser' => [
            'id' => '00000000-0000-0000-0000-000000000000',
            'name' => 'SYSTEM'
        ],
        'welcomeMessage' => [
            'enabled' => true,
            'projectName' => 'Project Name',
        ],
        'incomingTransports' => [
            'imap4' => 'IMAP'
        ],
        'outgoingTransports' => [
            'smtp' => 'SMTP'
        ],
        'remote_mailbox_messages' => [
            'markAsSeen' => false
        ],
        'local_mailbox_messages' => [
            'initialStatus' => 'new'
        ],
        'Mailbox' => [
            'default' => [
                'mailbox_type' => 'system',
                'incoming_transport' => 'internal',
                'incoming_settings' => 'default',
                'outgoing_transport' => 'internal',
                'outgoing_settings' => 'default',
                'mailbox_postfix' => '@system',
            ],
            'types' => [
                'system' => 'System',
            ],
        ],
        'Folder' => [
            'defaultType' => 'default',
        ],
    ]
];
