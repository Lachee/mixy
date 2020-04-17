<?php
namespace kiss\controllers;

//Little script to verify the DEBUG exists.
if (!defined("XVE_DEBUG")) define("XVE_DEBUG", false);

use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\router\Route;

class Controller extends Route {

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

    protected $headerFile = "@/views/base/header.php";
    protected $contentFile = "@/views/base/content.php";
    protected $exceptionFile = '@/views/base/error.php';

    /** Renders an exception */
    function renderException(HttpException $exception) {
        return $this->render($this->exceptionFile, [ 'exception' => $exception ]);
    }

    /** Renders the page. */
    public function render($action, $options = []) {
        $options['_VIEW'] = $this->renderContent($action, $options);
        $options['_CONTROLLER'] = $this;

        $html = '';
        if (!empty($this->headerFile)) $html .= $this->renderFile($this->headerFile, $options);
        if (!empty($this->contentFile)) $html .= $this->renderFile($this->contentFile, $options); else $html .= $options['_VIEW'];
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
            if (!startsWith($lwr, ':'))  $path .= '/' . $lwr;
        }
        
        if (empty($path))
            $path = '/main';

        $options['_CONTROLLER'] = $this;
        $filepath = (strpos($action, "@") === 0 ? $action : "@/views" . $path . "/" . $action) . ".php";
        return $this->renderFile($filepath, $options);
    }

    protected function setHeaderTemplate($file) { $this->headerFile = $file; return $this; }
    protected function setContentTemplate($file) { $this->contentFile = $file; return $this; }

    /** Renders a single file */
    private function renderFile($file, $_params_ = array()) {
        if (strpos($file, '@') === 0) {
            $file = \App::$xve->baseDir() . substr($file, 1);
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
        //Attempt to get the event
        $action = $this->getAction($endpoint);
        if ($action === false) {
            throw new HttpException(HTTP::NOT_FOUND, 'endpoint could not be found.');
        }

        //Perform the action
        $response = $this->{$action}();
        return \App::$xve->respond($response);
    }

    /** Gets the action name */
    protected function getAction($endpoint) {
        $endpoint = ucfirst(strtolower($endpoint));
        $action = "action{$endpoint}";
        if (!method_exists($this, $action)) { return false; }
        return $action;
    }
}