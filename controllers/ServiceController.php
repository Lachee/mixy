<?php
namespace controllers;

use App;
use core\controllers\Controller;
use helpers\HTTP;
use models\Service;

class ServiceController extends Controller {

    const SERVICE = 'service';
    
    /** @var \models\Service $service current service */
    public $service;

    public function action($endpoint) {
        /*
        Service infered from 
        1. Domain
        2. Query
        3. Session
        */

        return parent::action($endpoint);
        //Service from domain
        $this->service = Service::findDomain(HTTP::host())->one();

        //Service from query
        if ($this->service == null)
            $this->service = Service::findKey(HTTP::get(self::SERVICE, null))->one();

        //Service from session
        if ($this->service == null && App::$xve->session->isset(self::SERVICE))
            $this->service = Service::findKey(App::$xve->session->get(self::SERVICE, 'XVE'))->one();
 
        //Do normal action
        return parent::action($endpoint);
    }
}