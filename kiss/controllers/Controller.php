<?php
namespace kiss\controllers;

//Little script to verify the DEBUG exists.
if (!defined("XVE_DEBUG")) define("XVE_DEBUG", false);

use Exception;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\helpers\StringHelper;
use kiss\Kiss;
use kiss\router\Route;

class Controller extends Route {

    public const POS_START = 0;
    public const POS_END = 1;
    private $js = [];

    public static function getRouting() {
        $class = get_called_class();

        $lastIndex = strrpos($class, "Controller");
        if ($lastIndex !== false) {
            $name = substr($class, 0, $lastIndex);
        }

        $parts = explode('\\', $name);
        $count = count($parts);
        $route = '';

        if (strtolower($parts[$count - 2]) == strtolower($parts[$count - 1]))
            $count -= 1;

        for ($i = 2; $i < $count; $i++) {
            if (empty($parts[$i])) continue;

            $lwr = strtolower($parts[$i]);
            $route .= '/' . $lwr;
            
        }
        return $route;
    }

    /** Registers a constant variable to be declared */
    public function registerJsVariable($name, $value, $position = self::POS_START, $scope = 'const') {
        $name = str_replace(' ', '_', $name);
        $this->js[$position]["_$name"] = "{$scope} {$name} = " . json_encode($$value) . ";"; 
    }

    /** Registers some javascript */
    public function registerJs($js, $position = self::POS_END, $key = null) {
        $key = $key ?? md5($js);
        $this->js[$position][$key] = $js;
    }

    protected $headerFile = "@/views/base/header.php";
    protected $contentFile = "@/views/base/content.php";
    protected $footerFile = "@/views/base/footer.php";
    protected $exceptionView = '@/views/base/error';

    /** Renders an exception */
    function renderException(HttpException $exception) {
        return $this->render($this->exceptionView, [ 'exception' => $exception ]);
    }

    /** Renders the page. */
    public function render($action, $options = []) {
        $options['_VIEW'] = $this->renderContent($action, $options);
        $options['_CONTROLLER'] = $this;

        $html = '';
        if (!empty($this->headerFile)) $html .= $this->renderFile($this->headerFile, $options);
        if (!empty($this->contentFile)) $html .= $this->renderFile($this->contentFile, $options); else $html .= $options['_VIEW'];
        if (!empty($this->footerFile)) $html .= $this->renderFile($this->footerFile, $options);
        return $html;
    }

    /** Renders only the content */
    public function renderContent($action, $options = []) {
        $name = $class = get_called_class();
        $path = '';
        
        $lastIndex = strrpos($name, "Controller");
        if ($lastIndex !== false) { $name = substr($class, 0, $lastIndex); }
        $parts = explode('\\', $name);
        $count = count($parts);

        if (strtolower($parts[$count - 2]) == strtolower($parts[$count - 1]))
            $count -= 1;

        for ($i = 2; $i < $count; $i++) {
            if (empty($parts[$i])) continue;
            $lwr = strtolower($parts[$i]);
            if (!StringHelper::startsWith($lwr, ':'))  $path .= '/' . $lwr;
        }
        
        if (empty($path))
            $path = '/main';

        $options['_CONTROLLER'] = $this;
        $filepath = (strpos($action, "@") === 0 ? $action : "@/views" . $path . "/" . $action) . ".php";
        return $this->renderFile($filepath, $options);
    }

    /** Renders all the current js variables */
    private function renderJsVariables($position) {
        if (!isset($this->js[$position])) return '';

        $lines = [];
        foreach($this->js[$position] as $name => $def)
            $lines[] = $def;

        return '<script>' . join("\n", $lines) . '</script>';
    }

    protected function setHeaderTemplate($file) { $this->headerFile = $file; return $this; }
    protected function setContentTemplate($file) { $this->contentFile = $file; return $this; }

    /** Renders a single file */
    private function renderFile($file, $_params_ = array()) {
        if (strpos($file, '@') === 0) {
            $file = Kiss::$app->baseDir() . substr($file, 1);
        }

        $_obInitialLevel_ = ob_get_level();
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        try {
            if (XVE_DEBUG) {
                //Make sure the file exists
                if (!file_exists($file)) 
                {
                    printf("file " . $file . " does not exist.");
                    throw new HttpException(HTTP::INTERNAL_SERVER_ERROR, "View " . $file . " does not exist!");
                }
            }

            require $file;
            return ob_get_clean();
        } catch (\Exception $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            throw $e;
        }
    }

    /** Performs the endpoint's action */
    public function action($endpoint) {
        try {
            //Attempt to get the event
            $action = $this->getAction($endpoint);
            if ($action === false) {
                throw new HttpException(HTTP::NOT_FOUND, 'endpoint could not be found.');
            }

            //Perform the action
            $response = $this->{$action}();
            return Kiss::$app->respond($response);
        }catch(Exception $e) {
            return Response::exception($e);
        }
    }

    /** Gets the action name */
    protected function getAction($endpoint) {
        $endpoint = ucfirst(strtolower($endpoint));
        $action = "action{$endpoint}";
        if (!method_exists($this, $action)) { return false; }
        return $action;
    }
}