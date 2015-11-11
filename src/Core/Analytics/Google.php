<?php

namespace Core\Analytics;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Implements Google Analytics.
 *
 * Docs: https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide
 *
 * @package Core\Analytics
 */
class Google
{
    protected $gaCode;
    protected $host;
    //protected $cid;
    protected $client;

    protected $apiUrl = 'https://ssl.google-analytics.com';

    public function __construct($gaCode, $host/*, $cid*/)
    {
        $this->gaCode = $gaCode;
        $this->host = $host;
        //$this->cid = $cid;
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'http_errors' => false,
        ]);
    }

    public function trackPageView($page, $title = '', $params = [])
    {
        $params = array_merge($params, [
            't' => 'pageview',
            'dp' => '/'.trim($page, '/'),
            'dt' => $title,
        ]);

        return $this->send($params);
    }

    public function trackEvent($category, $action, $label = null, $value = null, $params = [])
    {
        $params = array_merge($params, [
            't' => 'event',
            'ec' => $category, // Event Category. Required.
            'ea' => $action,   // Event Action. Required.
            'el' => $label,    // Event label.
            'ev' => $value,    // Event value.
        ]);

        return $this->send($params);
    }

    protected function send(array $params)
    {
        if (empty($this->gaCode)) {
            return true;
        }

        $params['v'] = 1;
        $params['tid'] = $this->gaCode;
        $params['dh'] = $this->host;
        $params['v'] = 1;

        $request = new Request('POST', 'collect', [
            'body' => $params,
            'headers' => [
                'User-Agent' => client_user_agent(),
            ]
        ]);

        $response = $this->client->send($request);

        return $response->getStatusCode() === 200;
    }
}
