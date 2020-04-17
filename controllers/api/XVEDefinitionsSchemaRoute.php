<?php
namespace controllers\api;

use App;
use Exception;
use exception\HttpException;
use helpers\HTTP;
use router\Route;
use xve\definition\Definition;

/* GET node types */
class XVEDefinitionsSchemaRoute extends Route {

    /** {@inheritdoc} */
    public static function getRouting() { return "/xve/definitions/schema"; }

    /** {@inheritdoc} */
    public function get() {
     
        //Validate the type
        $class = HTTP::get('class', null);
     
        try {
            if ($class == null) throw new HttpException(HTTP::BAD_REQUEST);
            if (!is_subclass_of($class, Definition::class)) throw new HttpException(HTTP::BAD_REQUEST, "{$class} is not a definition");
        }catch(\Exception $e) {
            throw new HttpException(HTTP::BAD_REQUEST, $e);
        }

        //Create a blank version of the definition
        $config = App::$xve->getXveConfigurator();
        return $class::getJsonSchema([ 'xve' => $config ]);
    }
}