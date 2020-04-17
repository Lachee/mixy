<?php
namespace controllers\api;

use exception\HttpException;
use helpers\HTTP;
use router\Route;

/** GET Lists projects a user has
 * POST creates a new project
 */
class UserProjectRoute extends Route {

    public $snowflake;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/user/:snowflake/project"; }

    //Return a list of projects the user has
    public function get() {
        return \models\Project::findOwner($this->snowflake)->all();
    }

    //Create a new project
    public function post($data) {
        if (empty($this->snowflake))
            throw new HttpException(HTTP::BAD_REQUEST, "missing owner");

        if (empty($data['title']))
            throw new HttpException(HTTP::BAD_REQUEST, "empty name");

        $project = new \models\Project();
        $project->title = $data['title'];
        $project->description = $data['description'] ?? '';
        $project->owner = $this->snowflake;
        $project->id = $project->save();
        return $project;
    }
}