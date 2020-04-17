<?php
namespace controllers\api;

use App;
use helpers\HTTP;
use router\Route;

/* GET node types */
class XVEDefinitionsRoute extends Route {

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/xve/definitions"; }

    //HTTP GET on the route. Return an object and it will be sent back as JSON to the client.
    // Throw an exception to send exceptions back.
    // Supports get, delete
    public function get() {
       $xve = App::$xve->getXveConfigurator();
       $definitions = $xve->getDefinitions();

       //Check if it is filtered. If it is then prepeare a new filtered array
       $typeFilter = HTTP::get('type', null);
       if (!empty($typeFilter)) {

            //filter the definitions and return that
            $filtered = [];
            foreach($definitions as $def) {
                if ($def->className() == $typeFilter || is_subclass_of($def->className(), $typeFilter)) {
                    $filtered[] = $def;
                }
            }

            return $filtered;
       }

       //Return all the definitions
       return $definitions;
    }
}