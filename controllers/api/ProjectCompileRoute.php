<?php
namespace controllers\api;

use exception\HttpException;
use helpers\HTTP;
use models\GraphRecord;
use router\Route;
use xve\compiler\ProjectCompiler;

/** GET Compiles project */
class ProjectCompileRoute extends Route {

    //We can define the property, but it's not required. The router doesn't care if it exists or not, it will set it (creating it if it doesn't exist).
    public $id;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/project/:id/compile"; }

    //Gets the compiled project
    public function get() {

        //Fetch the events
        $events = GraphRecord::findProjectEvents($this->id)->all();

        //Compile the project
        $compiler = new ProjectCompiler();
        $compilation = $compiler->compileGraphRecords($events);
        return \JShrink\Minifier::minify($compilation);
    }

}