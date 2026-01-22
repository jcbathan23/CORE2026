<?php

return [
    'SMTP_HOST'   => 'smtp.gmail.com',
    'SMTP_PORT'   => 465,  // Using SSL port
    'SMTP_SECURE' => 'ssl', // Using SSL instead of TLS
    'SMTP_AUTH'   => true,
    'SMTP_DEBUG'  => 1, // Enable debugging (0 = off, 1 = client messages, 2 = client and server messages)
    'SMTP_OPTIONS' => [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
            'crypto_method'     => STREAM_CRYPTO_METHOD_ANY_CLIENT
        ]
    ],
    'SMTP_TIMEOUT' => 30, // Timeout in seconds
    'SMTP_USER'    => 'bathanjc23@gmail.com',
    'SMTP_PASS'    => 'vklamahiuzwyqbtr',
    'FROM_EMAIL'   => 'bathanjc23@gmail.com',
    'FROM_NAME'    => 'SLATE'
];
