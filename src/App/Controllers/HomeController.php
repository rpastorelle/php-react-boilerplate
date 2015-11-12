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
            $this->app->view->display($page.'.html', $this->getTemplateData($data));
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
