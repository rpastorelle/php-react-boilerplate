<?php
namespace App\Controllers;

use Core\Application;
use Core\BaseController;
use GuzzleHttp\Psr7\Request;

class HomeController extends BaseController
{
    /**
     * @var array
     */
    private $metadata;

    function __construct(Application $app)
    {
        parent::__construct($app);

        $this->metadata = [
            'title' => 'Home',
            'description' => 'Homepage description',
            'ga_tracking_id' => $this->app->config('ga.tracking_id'),
        ];
    }

    /**
     * Render page template
     *
     * @param      $page
     * @param null $data
     */
    private function _renderPage($page, $data = [])
    {
        try {
            $data = array_merge($data, ['metadata' => $this->metadata]);
            $this->app->view->display($page.'.html', $data);
        } catch (\RuntimeException $e) {
            //throw new NotFoundException('Page not found');
        }
    }

    /**
     * GET /
     */
    public function index()
    {
        $this->_renderPage('home');
    }

}
