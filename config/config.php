<?php

$env = strtolower(getenv('ENV'));
$apiVersion = 'v1';
$apiHost = 'https://api.phpreactboilerplate.com';
switch ($env) {
    case 'stage':
        $apiHost = 'https://stage-api.phpreactboilerplate.com';
        break;

    case 'dev':
        $apiHost = 'http://dev-api.phpreactboilerplate.com';
        break;
}

return [

    'ENV' => $env,

    'api.version' => $apiVersion,
    'api.host'    => $apiHost,
    'api.base'    => $apiHost.'/'.$apiVersion.'/',
    'api.key'     => '',

    'routes.case_sensitive'       => true,
    'routes.controller_namespace' => '\\App\\Controllers\\',

    'templates.path' => $this->basePath.'/templates',
    'view'           => new \Slim\Views\Twig(),

    'ga.tracking_id' => '',
];
