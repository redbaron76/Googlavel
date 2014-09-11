<?php

return [

    // OAuth2 Setting, you can get these keys in Google Developers Console
    'oauth2_client_id'      => '< YOUR CLIENT ID >',
    'oauth2_client_secret'  => '< YOUR CLIENT SECRET >',
    'oauth2_redirect_uri'   => 'http://localhost:8081/',   // Change it according to your needs

    // Definition of service specific values like scopes, OAuth token URLs, etc
    'services' => array(

        'calendar' => array(
            'scope' => 'https://www.googleapis.com/auth/calendar'
        ),
        /*'books' => [
            'scope' => 'https://www.googleapis.com/auth/books'
        ]*/

    ),

    // Service file name prefix
    'service_class_prefix' => 'Google_Service_',

    // Custom settings
    'access_type' => 'online',    
    'approval_prompt' => 'auto',

];