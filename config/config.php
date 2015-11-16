<?php
if (! getenv('ENV') || getenv('ENV') === 'dev') {
    (new Dotenv\Dotenv('../'))->load();
}

$env = strtolower(getenv('ENV'));
$apiVersion = 'v1';
$apiHost = 'https://api.phpreactboilerplate.com';
// Perhaps S3 or CDN url:
$assetsUrl = '';
$assetsPath = $assetsUrl;
$assets = json_decode(file_get_contents('../asset-manifest.json'), true);
switch ($env) {
    case 'stage':
        $apiHost = 'https://stage-api.phpreactboilerplate.com';
        break;

    case 'dev':
        $apiHost = 'http://dev-api.phpreactboilerplate.com';
        $assets = array_combine(array_keys($assets), array_keys($assets));
        $assetsPath = '/build';
        break;
}

$container = new Slim\Container([
    'settings' => [
        'env' => $env,
        'base_path' => realpath(__DIR__.'/../'),

        'app.name'        => 'PHP React Boilerplate',
        'app.description' => 'A boilerplate for PHP-React JS web apps.',
        'app.keywords'    => 'php,reactjs,web apps',

        'api.version' => $apiVersion,
        'api.host'    => $apiHost,
        'api.base'    => $apiHost.'/'.$apiVersion.'/',
        'api.key'     => '',

        'app.urls.assets' => $assetsUrl,
        'app.paths.js'    => $assetsPath.'/js',
        'app.paths.css'   => $assetsPath.'/css',
        'app.assets'      => $assets,

        'ga.tracking_id' => '',
    ],
]);


/***** Service Definitions *****/

$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig($c['settings']['base_path'].'/resources/templates');
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));

    return $view;
};

$container['api_client'] = function ($c) {
    return new GuzzleHttp\Client([
        'base_uri' => $c['settings']['api.base'],
        'http_errors' => false,
        'headers' => [
            'Api-Key' => $c['settings']['api.key'],
        ],
    ]);
};

$container['ga'] = function ($c) {
    return new Core\Analytics\Google($c['settings']['ga.tracking_id'], http_host());
};

return $container;
