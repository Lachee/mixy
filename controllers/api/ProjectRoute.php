<?php
namespace controllers\api;

use exception\HttpException;
use helpers\HTTP;
use models\Project;
use router\Route;

/**
 * GET Entire project information
 * PUT Updates project information
 */
class ProjectRoute extends Route {

    //We can define the property, but it's not required. The router doesn't care if it exists or not, it will set it (creating it if it doesn't exist).
    public $id;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/project/:id"; }

    //Return the project information + list of graph IDs
    public function get() {
        return $this->getProject();
    }

    //Update the project information
    public function put($data) {

        if (empty($data['title']))
            throw new HttpException(HTTP::BAD_REQUEST, "empty title");

        $project = $this->getProject();
        $project->title = $data['title'] ?? $project->title;
        $project->description = $data['description'] ?? $project->description;

        if (empty($project->title))
            throw new HttpException(HTTP::BAD_REQUEST, "empty name");

        if ($project->save()) {
            return $project;
        }

        return $this->getProject();
    }

    private function getProject() {
        $model = Project::findByKey($this->id)->one();
        if ($model == null) throw new HttpException(HTTP::NOT_FOUND, "project was not found.");
        return $model;
    }
}