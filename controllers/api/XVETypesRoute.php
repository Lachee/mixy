<?php
namespace controllers\api;

use App;
use router\Route;

/* GET node types */
class XVETypesRoute extends Route {

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/xve/types"; }

    //HTTP GET on the route. Return an object and it will be sent back as JSON to the client.
    // Throw an exception to send exceptions back.
    // Supports get, delete
    public function get() {
       $xve = App::$xve->getXveConfigurator();
       return $xve->getTypes();
    }
}