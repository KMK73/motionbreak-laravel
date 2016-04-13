<?php
return array(
    'appNameIOS'     => array(
        'environment' =>'development',
        //Path to the 'app' folder
        'certificate'=>app_path().'/myCert.pem',
        'passPhrase'  =>'',
        'service'     =>'apns'
    ),
    'appNameAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )
);