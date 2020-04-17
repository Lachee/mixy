<?php
namespace controllers\api;

use App;
use Exception;
use exception\HttpException;
use helpers\HTTP;
use models\StoredType;
use router\Route;
use xve\definition\Definition;
use xve\type\ValueType;

/* GET node types */
class XVETypesSchema extends Route {

    /** {@inheritdoc} */
    public static function getRouting() { return "/xve/types/schema"; }

    /** {@inheritdoc} */
    public function get() {
     
        //Create a blank version of the definition
        $schema = StoredType::getJsonSchema();
        return $schema;
    }
}