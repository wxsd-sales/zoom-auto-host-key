<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'webex' => [
        'client_id' => env('WEBEX_CLIENT_ID'),
        'client_secret' => env('WEBEX_CLIENT_SECRET'),
        'redirect' => env('WEBEX_REDIRECT_URI', '/auth/webex/callback'),
        'scopes' => explode(' ', env('WEBEX_SCOPES', 'spark:people_read spark:kms')),
        'require_role' => env('WEBEX_REQUIRE_ROLE', 'Y2lzY29zcGFyazovL3VzL1JPTEUvaWRfZnVsbF9hZG1pbg'),
        'oauth_url' => env('WEBEX_OAUTH_URL', 'https://webexapis.com/v1/access_token'),
    ],
];
