<?php

/*
 * This file is part of composer package wanghanlin/laravel-mobile-push.
 *
 *  (c) 2016 Wang Hanlin <admin@wanghanlin.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
return [
    'ios' => [
        'env'     => 'dev',
        'cert'    => '/path/to/certificate.pem',
        'pass'    => 'password',
        'adapter' => 'apnsPush',
    ],
    'iosFeedback' => [
        'env'     => 'prod',
        'cert'    => '/path/to/certificate.pem',
        'pass'    => 'password',
        'adapter' => 'apnsFeedback',
    ],
    'android' => [
        'apiKey'  => 'somerandomstring',
        'adapter' => 'gcmPush',
    ],
];
