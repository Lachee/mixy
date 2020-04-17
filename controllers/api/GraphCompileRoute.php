<?php
namespace controllers\api;

use App;
use exception\HttpException;
use helpers\HTTP;
use models\StoredGraph;
use router\Route;

/* GET a compiliation of the graph. */
class GraphCompileRoute extends Route {

    //We can define the property, but it's not required. The router doesn't care if it exists or not, it will set it (creating it if it doesn't exist).
    public $id;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/graph/:id/compile"; }

    //Gets the compiled graph
    public function get() {

        /** @var StoredGraph */
        $stored = StoredGraph::findByKey($this->id)->one();
        if ($stored == null) return new HttpException(HTTP::NOT_FOUND, "graph not found");

        //Compile the graph
        $configurator = App::$xve->getXveConfigurator();
        return $configurator->getGraphAST($this->id);
    }
}