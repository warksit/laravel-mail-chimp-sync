<?php

return [
    /**
     * Your Mailchimp Auth Type
     * basic or oauth
     *
     * @type string
     */
    'default' => 'basic',

    'auths' => [
        'basic' => [
            'api_key' => '',
            'end_point' => '',
        ],
        'oauth' => [
            'access_token' => '',
            'end_point' => '',
        ],
    ],
];