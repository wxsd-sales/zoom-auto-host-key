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
        'api_url' => env('WEBEX_API_URL', 'https://webexapis.com/v1'),
        'client_id' => env('WEBEX_CLIENT_ID'),
        'client_secret' => env('WEBEX_CLIENT_SECRET'),
        'redirect' => env('WEBEX_REDIRECT_URI', '/auth/webex/callback'),
        'scopes' => explode(' ', env('WEBEX_SCOPES', 'spark:people_read spark:kms')),
        'require_role' => env('WEBEX_REQUIRE_ROLE', 'Y2lzY29zcGFyazovL3VzL1JPTEUvaWRfZnVsbF9hZG1pbg'),
        'oauth_url' => env('WEBEX_OAUTH_URL', 'https://webexapis.com/v1/access_token'),
        'workspace_integration' => [
            'manifest_version' => 1,
            'display_name' => env('APP_NAME', 'Zoom Auto Host Key'),
            'vendor' => 'Webex Solution Developers',
            'email' => 'wxsd@external.cisco.com',
            'description' => 'PoC application to host Zoom CRC meetings from RoomOS devices without the need for '.
                'host key.',
            'availability' => 'org_private',
            'api_access' => [
                [
                    'scope' => 'spark-admin:devices_read',
                    'access' => 'required',
                    'name' => 'See details for any device in your organization',
                    'description' => 'Lookup and ensure device belongs to the organization',
                ],
                [
                    'scope' => 'spark-admin:people_read',
                    'access' => 'required',
                    'name' => "Access to read your user's company directory",
                    'description' => 'Lookup and ensure user belongs to the organization',
                ],
                [
                    'scope' => 'spark:messages_read',
                    'access' => 'required',
                    'name' => 'Read the content of rooms that you are in',
                    'description' => 'Notify users, create application alerts, and logging',
                ],
                [
                    'scope' => 'spark:messages_write',
                    'access' => 'required',
                    'name' => 'Post and delete messages on your behalf',
                    'description' => 'Notify users, create application alerts, and logging',
                ],
                [
                    'scope' => 'spark:rooms_read',
                    'access' => 'required',
                    'name' => 'List the titles of rooms that you are in',
                    'description' => 'Notify users, create application alerts, and logging',
                ],
                [
                    'scope' => 'spark:rooms_write',
                    'access' => 'required',
                    'name' => 'Manage rooms on your behalf',
                    'description' => 'Notify users, create application alerts, and logging',
                ],
                [
                    'scope' => 'spark:memberships_read',
                    'access' => 'required',
                    'name' => 'List people in the rooms you are in',
                    'description' => 'Notify users, create application alerts, and logging',
                ],
                [
                    'scope' => 'spark:memberships_write',
                    'access' => 'required',
                    'name' => 'Invite people to rooms on your behalf',
                    'description' => 'Notify users, create application alerts, and logging',
                ],
                [
                    'scope' => 'spark:xapi_statuses',
                    'access' => 'required',
                    'name' => 'Retrieve all information from RoomOS-enabled devices',
                    'description' => 'Mandatory when requesting granular xAPI Status permissions',
                ],
                [
                    'scope' => 'spark:xapi_commands',
                    'access' => 'required',
                    'name' => 'Execute all commands on RoomOS-enabled devices',
                    'description' => 'Mandatory when requesting granular xAPI Command permissions',
                ],
            ],
            'xapi_access' => [
                'status' => [
                    [
                        'path' => 'SystemUnit.State.NumberOfActiveCalls',
                        'access' => 'required',
                        'name' => 'Shows the number of active calls.',
                        'description' => 'Receive notification when device is in an active call',
                    ],
                    [
                        'path' => 'Call[*].*',
                        'access' => 'required',
                        'name' => 'Shows information related to an ongoing call.',
                        'description' => 'Check if the active call is an outgoing Zoom CRC call',
                    ],
                ],
                'events' => [
                    [
                        'path' => 'UserInterface.Message.TextInput.Response',
                        'access' => 'required',
                        'name' => 'Triggered when user responds to an input dialog box.',
                        'description' => 'Identify input for Zoom host account',
                    ],
                ],
                'commands' => [
                    [
                        'path' => 'UserInterface.Message.TextInput.Display',
                        'access' => 'required',
                        'name' => 'Displays an input dialog box to which a user can respond.',
                        'description' => 'Display Zoom host account input field prompt',
                    ],
                    [
                        'path' => 'UserInterface.Message.TextInput.Clear',
                        'access' => 'required',
                        'name' => 'Remove the text input message which was displayed using the UserInterface Message '.
                            'TextInput Display command.',
                        'description' => 'Clear Zoom host account input field prompt',
                    ],
                    [
                        'path' => 'Call.DTMFSend',
                        'access' => 'required',
                        'name' => 'Send DTMF tones to the far end.',
                        'description' => 'Send the host-key when on a Zoom CRC call',
                    ],
                ],
            ],
            'provisioning' => [
                'type' => 'manual',
            ],
        ],
        'jwk' => [
            'us-west-2_r' => 'https://xapi-r.wbx2.com/jwks',
            'us-east-2_a' => 'https://xapi-a.wbx2.com/jwks',
            'eu-central-1_k' => 'https://xapi-k.wbx2.com/jwks',
        ],
    ],

    'zoom' => [
        'api_url' => env('ZOOM_API_URL', 'https://api.zoom.us/v2'),
        'oauth_url' => env('ZOOM_OAUTH_URL', 'https://zoom.us/oauth/token'),
        'server_to_server' => [
            'scopes' => [
                [
                    'id' => 'meeting:read:admin',
                    'name' => 'View all user meetings',
                    'description' => 'Retrieve the host for an on device Zoom Meeting',
                ],
                [
                    'id' => 'meeting:write:admin',
                    'name' => 'View and manage all user meetings',
                    'description' => 'Add machine/room account as host for an on device Zoom Meeting',
                ],
                [
                    'id' => 'user:read:admin',
                    'name' => 'View all user information',
                    'description' => 'Lookup and Ensure user belongs to the organization',
                ],
                [
                    'id' => 'user:write:admin',
                    'name' => 'View users information and manage users',
                    'description' => "Update an account's (user or machine/room) host key",
                ],
            ],
        ],
    ],

];
