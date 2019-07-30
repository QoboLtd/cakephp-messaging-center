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
        'allowedMailFolders' => [
            'INBOX',
            'Sent Items'
        ]
    ]
];
