<?php
namespace controllers\api;

use App;
use exception\HttpException;
use helpers\HTTP;
use models\GraphRecord;
use models\Project;
use models\StoredGraph;
use router\Route;
use xve\graph\node\NodeType;
use xve\graph\node\IONodeEventType;

/** GET Lists all graphs in the project
 *  POST creates new graph
 */
class ProjectGraphRoute extends Route {

    //We can define the property, but it's not required. The router doesn't care if it exists or not, it will set it (creating it if it doesn't exist).
    public $id;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/project/:id/graph"; }

    //Gets a list of graphs of the project
    public function get() {
        return StoredGraph::findMetadataByProject($this->id)->all(true);
    }

    //Creates a new graph for the project
    public function post($data) {
    
        $project = Project::findByKey($this->id)->one();
        if ($project == null) throw new HttpException(HTTP::NOT_FOUND, "project was not found.");

        if (empty($data['title']))
            throw new HttpException(HTTP::BAD_REQUEST, "empty title");

        if (empty($data['type']))
            throw new HttpException(HTTP::BAD_REQUEST, "empty type");

        if ($data['type'] != 'COMMAND' && $data['type'] != 'EVENT' && $data['type'] != 'SUBGRAPH')
            throw new HttpException(HTTP::BAD_REQUEST, "invalid type");
        
        if ($data['type'] == 'EVENT' && !self::validateEvent($data['event']))
            throw new HttpException(HTTP::BAD_REQUEST, "invalid event");

        if ($data['type'] == 'COMMAND' && empty($data['event']))
            throw new HttpException(HTTP::BAD_REQUEST, "invalid command");

        $g = new StoredGraph($data);
        $g->setProject($project);
        
        if (!$g->save()) { 
            throw new HttpException(HTTP::INTERNAL_SERVER_ERROR, "failed to save graph.");
        }

        return $g;
    }

    /** Validates that the event name is actually an event */
    private static function validateEvent($event) {
        $configurator = App::$xve->getXveConfigurator();
        $definitions = $configurator->getDefinitions();
        foreach($definitions as $def) {
            if ($def instanceof \xve\definition\EventDefinition) {
                if ($def->getTypeName() == $event)
                    return true;
            }
        }
        return false;
    }

    /** Creates a empty subgraph */
    private static function createSubnodeGraph() { 
        return <<<EOD
{"last_node_id":4,"last_link_id":2,"nodes":[{"id":3,"type":"io/input","pos":[100,170],"size":[160,36],"flags":{},"title":"Graph IN","properties":{"slots":[{"index":"0","type":"-1","name":"then","label":"then","locked":true},{"index":"1","type":"object","name":"New Slot","locked":false}],"type":"output"},"properties_info":[]},{"id":4,"type":"io/output","pos":[430,170],"size":[160,36],"flags":{},"title":"Graph OUT","properties":{"slots":[{"index":"0","type":"-1","name":"do","label":"do","locked":true}],"type":"input"},"properties_info":[]}],"links":[null,{"c":[3,0,4,0],"a":[]}],"input":null,"output":null}
EOD;
    }
}