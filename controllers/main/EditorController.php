<?php
namespace controllers\main;
use controllers\ServiceController;
use exception\HttpException;
use helpers\HTTP;
use helpers\Response;
use models\GraphRecord;
use models\Project;
use xve\compiler\ProjectCompiler;
class EditorController extends ServiceController {

    public $projectId;
    public $debug = false;
    public $createMissingProjects = false;

    public $destroy_export = false;
    public $minify_export = false;

    public static function getRouting() { return "/editor/:projectId"; }
    function __construct()
    {
        $this->setHeaderTemplate('@/views/editor/header.php');
        $this->setContentTemplate(null);
    }

    function actionCreate() {
        $this->createMissingProjects = true;
        $this->getProject();
        return Response::redirect("@");
    }

    function actionDebug() {
        $this->debug = true;
        return $this->actionIndex();
    }

    function actionIndex() {
        //NodeType::include_once();
        //$types = [];
        //foreach(NodeType::getTypes() as $key => $type) {
        //    if (!$type->visible) continue;
        //    $parts = explode('/', $key);
        //    $types[$parts[0]][] = $type;
        //}

        return $this->render('editor', [
            'title' => 'XVE ' . $this->projectId,
            'project' => $this->getProject(),
            'minflag' => ($this->debug ? '?cache=false&minimise=false' : ''),
            //'nodeGroups' => $types,
        ]);
    }

    function actionExport() {

        //Fetch the events
        $events = GraphRecord::findProjectEvents($this->projectId)->all();

        //Minify the export if we should
        if (HTTP::get('min', false, FILTER_VALIDATE_BOOLEAN)) {
            $this->minify_export = true;   
        }
        
        //Compile the project
        $compiler = new ProjectCompiler();
        $code = $compiler->compileGraphRecords($events);
        $min = $this->minify_export ? \JShrink\Minifier::minify($code, ['flaggedComments' => false]) : $code;

        if ($this->destroy_export) {
            $min = "/** THIS IS FOR DEMOSTRATION PURPOSES ONLY. THIS MAY CONTAIN BUGS */\n\n" . $min;
            $min = str_replace('false', 'true', $min);
            $min = str_replace('true', 'false', $min);
            $min = str_replace('\'', 'ߴ‎', $min);
            $min = str_replace('}', '❵', $min);
            $min = str_replace('{', '𝄔', $min);
            $min = str_replace('w', '𝚠', $min);
            $min = str_replace('n', '𝗇', $min);
            $min = str_replace('e', 'е', $min);
        }


        return Response::javascript($min);
        //return Response::file('bot.js', $min);
    }


    private function getProject() {
        $p = Project::findByKey($this->projectId)->one();
        if ($p == null) {
            if ($this->createMissingProjects) {
                $p = new Project();
                $p->id = $this->projectId;
                $p->owner = 0;
                $p->setTitle("New Project")->setDescription("procedually generated project because the ID was missing");
                $p->save();
            } else {
                throw new HttpException(HTTP::NOT_FOUND, "project could not be found"); 
            }
        }
        return $p;
    }
}