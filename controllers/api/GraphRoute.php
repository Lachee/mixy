<?php
namespace controllers\api;

use exception\HttpException;
use helpers\HTTP;
use models\StoredGraph;
use router\Route;

/* GET Complete graph information 
* DELETE Deletes the graph
* PUT Updates the graph, compiling if data has changed.
*/
class GraphRoute extends Route {

    //We can define the property, but it's not required. The router doesn't care if it exists or not, it will set it (creating it if it doesn't exist).
    public $id;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/graph/:id"; }

    //Gets a list of graph ids
    public function get() {
        return $this->getGraph();
    }

    //Delete the graph
    public function delete() {
        $graph = $this->getGraph();
        return StoredGraph::findByKey($graph->id)->delete()->limit(1)->execute();
    }

    //Update's the graph
    public function put($data) {

        //Get the stored graph
        $graph = $this->getGraph();

        //Attempt to set its data
        if (!empty($data['data'])) {
            $graph->data = $data['data'];
            //$graph->compile(true);
        }

        //Update just some metadata
        $graph->title   = $data['title'] ?? $graph->title;
        $graph->event   = $data['event'] ?? $graph->event;
        $graph->display = $data['display'] ?? $graph->display;

        //Validate the fields
        if (isset($data['type']) && $graph->type != $data['type'])
            throw new HttpException(HTTP::BAD_REQUEST, "cannot change type");

        if (empty($graph->title))
            throw new HttpException(HTTP::BAD_REQUEST, "title cannot be empty");

        if (strlen($graph->event) >= 128)
            throw new HttpException(HTTP::BAD_REQUEST, "event is too long");

        if ($graph->save() === false) {
            throw new HttpException(HTTP::INTERNAL_SERVER_ERROR, "failed to save graph");
        }

        //Return the graph
        return $graph;
    }

    /** @return StoredGraph the stored graph this request is making. */
    private function getGraph()  {
        $graph = StoredGraph::findByKey($this->id)->one();
        if ($graph == null) throw new HttpException(HTTP::NOT_FOUND, "graph was not found");
        return $graph;
    }
}