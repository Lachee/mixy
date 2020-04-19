<?php namespace app\controllers;

use kiss\controllers\Controller;

/** Controller for oAuth */
class OauthController extends Controller {

    public static function getRouting() { return "/oauth";}

    public function action($endpoint) {
        $endpoint = ucfirst(strtolower($endpoint));
    
    }
}