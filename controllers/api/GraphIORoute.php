<?php
namespace controllers\api;

use exception\HttpException;
use helpers\HTTP;
use models\GraphRecord;
use xve\graph\Graph;
use router\Route;

/** Get metadata of connecting node for the IO components. */
class GraphIORoute extends Route {

    //We can define the property, but it's not required. The router doesn't care if it exists or not, it will set it (creating it if it doesn't exist).
    public $id;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/graph/:id/io"; }

    //Gets a list of graph ids
    public function get() {
        $record = $this->getGraph();
        $graph = $record->getGraph();
        $exit = $graph->getExitNode();
        $entry = $graph->getEntryNode();

        if ($entry == null) 
            throw new HttpException(HTTP::INTERNAL_SERVER_ERROR, "graph has no valid entry node");
            
        return [
            'id'        => $record->id,
            'title'     => $record->title,
            'project'   => $record->project,
            'type'      => $record->type,
            'event'     => $record->event,
            'entry'     => $entry != null  ? $entry->getProperty('slots', []) : [], 
            'exit'      => $exit != null   ? $exit->getProperty('slots', []) : [],
        ];
    }


    private function getGraph() : GraphRecord {
        /** @var GraphRecord $graph */
        $graph = GraphRecord::findKey($this->id)->one();
        if ($graph == null) throw new HttpException(HTTP::NOT_FOUND, "graph was not found");
        return $graph;
    }
}