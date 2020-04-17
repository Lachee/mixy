<?php
namespace controllers\api;

use App;
use router\Route;
use db\Query;
use helpers\HTTP;
use helpers\Response;
use router\RouteFactory;
use xve\configuration\FileConfigurator;
use xve\graph\ObjectType;
use xve\definition\Definition;

class AstRoute extends Route {

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/ast"; }

    //HTTP GET on the route. Return an object and it will be sent back as JSON to the client.
    // Throw an exception to send exceptions back.
    // Supports get, delete
    public function get() {

        //https://gist.github.com/Lachee/92b2c2107b9bd213536007eb7d5d5016
        
        //ObjectType::include_once();
        //return ObjectType::getTypes();

        //Prepare the config and load all the definitions
        $fileConfigurator = new FileConfigurator();
        $fileConfigurator->load();

        $js = "";
        foreach($fileConfigurator->getDefinitions() as $typeName => $definition) {
            $js .= "//{$typeName}:\n";
            $js .= $definition->generateJavascriptPrototype() . "\n\n";
        }

        App::$xve->setDefaultResponseType(HTTP::CONTENT_APPLICATION_JAVASCRIPT);
        return $js;

        return $fileConfigurator->getDefinitions();
        return $fileConfigurator->getTypes();


        $astCompiler = new \xve\ast\Compiler();
        return $astCompiler->test();
    }
}