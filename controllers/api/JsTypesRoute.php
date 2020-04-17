<?php
namespace controllers\api;

use App;
use helpers\HTTP;
use helpers\Response;
use JShrink\Minifier;
use router\Route;

/** GET Node Prototypes */
class JsTypesRoute extends Route {

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher prioriypesty. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/js/types"; }

    //HTTP GET on the route. Return an object and it will be sent back as JSON to the client.
    // Throw an exception to send exceptions back.
    // Supports get, delete
    public function get() {

        //Get the cached redis. If it exists then return the resposne
        if (!HTTP::get('cache', false)) {
            $cached = App::$xve->redis()->get('js:nodes:types');
            if (!empty($cached)) { 
                return Response::javascript($cached)->respond();
            }
        }

        //Prepare everything
        $xve = App::$xve->getXveConfigurator();
        $js = "";
        
        //Generate the file
        foreach($xve->getTypes() as $typeName => $type) {
            $json = json_encode($type);
            $js .= "//{$typeName}:\n";
            $js .= "LiteGraph.addType('{$type->getName()}', {$json});\n";
        }
        
        //Minimise JS
        if (HTTP::get('minimise', true, FILTER_VALIDATE_BOOLEAN)) {
            $js = Minifier::minify($js);
        }
        
        //Cache it and return the response
        App::$xve->redis()->set('js:nodes:types', $js);
        return Response::javascript($js)->respond();
    }

    /** Deletes the redis cache */
    public function delete() {
        App::$xve->redis()->del('js:nodes:types');
        return true;
    }
}