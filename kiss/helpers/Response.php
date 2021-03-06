<?php

namespace kiss\helpers;

use controllers\main\MainController;
use \Exception;
use kiss\controllers\Controller;
use kiss\exception\HttpException;
use kiss\Kiss;
use kiss\models\BaseObject;

class Response {
    
    private $status;
    private $headers;
    private $contentType;
    private $content;

    function __construct($status, $headers, $content, $contentType)
    {
        $this->status = $status;
        $this->headers = $headers ?? [];
        $this->content = $content;
        $this->contentType = $contentType;
    }

    /** Creates a new response to handle the exception. If the supplied mode is null, it will use the server's default. */
    public static function exception(Exception $exception, $status = HTTP::INTERNAL_SERVER_ERROR, $mode = null) {
        $response = $status;
        if ($exception instanceof HttpException) return self::httpException($exception, $mode);
        return self::httpException(new HttpException($status, $exception), $mode);
    }

    /** Creates a new response to handle the exception. If the supplied mode is null, it will use the server's default. */
    public static function httpException(HttpException $exception, $mode = null) {
        if ($mode == null) $mode = Kiss::$app->getDefaultResponseType();
        switch ($mode){
            default:
            case HTTP::CONTENT_TEXT_PLAIN:
                return self::text($exception->getStatus(), $exception->getMessage());

            case HTTP::CONTENT_APPLICATION_JSON:
                return self::json($exception->getStatus(), $exception->getException(), $exception->getMessage());

            case HTTP::CONTENT_TEXT_HTML:
                try {
                    //Try to get the controller and execute the actionException on it
                    $controllerClass = Kiss::$app->mainController;
                    $controller = BaseObject::new(Kiss::$app->mainController);
                    $response = $controller->action('exception', $exception);
                    //$response = $controller->renderException($exception);
                    return self::html($exception->getStatus(), $response);
                } catch(Exception $ex) { 
                    //An error occured, so we ill just use the default plain text handling
                    return self::httpException($exception, HTTP::CONTENT_TEXT_PLAIN);
                }
        }
    }

    /** Creates a new plain text response */
    public static function text($status, $data) {
        return new Response($status, [], $data, HTTP::CONTENT_TEXT_PLAIN);
    }

    /** Creates a new json response */
    public static function json($status, $data, $message = '') {
        return new Response($status, [], ['status' => $status, 'message' => $message, 'data' => $data ], HTTP::CONTENT_APPLICATION_JSON);
    }

    /** Creates a new html response */
    public static function html($status, $data) {
        return new Response($status, [], $data, HTTP::CONTENT_TEXT_HTML);
    }

    /** Creates a new file response */
    public static function file($filename, $data) {
        return new Response(HTTP::OK, [ 
            'Content-Transfer-Encoding' => 'Binary', 
            'Content-disposition' => 'attachment; filename="'.$filename.'"' 
        ], $data, HTTP::CONTENT_APPLICATION_OCTET_STREAM);
    }

    /** Creates a new javascript response */
    public static function javascript($data) {
        return new Response(HTTP::OK, [], $data, HTTP::CONTENT_APPLICATION_JAVASCRIPT);
    }

    /** Creates a new redirect response */
    public static function redirect($location) {
        return (new Response(HTTP::OK, [], "redirecting", HTTP::CONTENT_TEXT_PLAIN))->setLocation($location);
    }

    /** Sets a header and returns the response. */
    public function setHeader($header, $value) {
        $this->headers[$header] = $value;
        return $this;
    }

    /** Sets the content type and returns the response. */
    public function setContentType($type) {
        $this->contentType = $type;
        return $this;
    }

    /** Sets the location header and returns the response. */
    public function setLocation($location) {
        $this->headers['location'] = HTML::href($location);
        return $this;
    }

    /** Sets the response contents and returns the response itself. */
    public function setContent($content, $contentType = null) {
        $this->content = $content;
        $this->contentType = $contentType ?? $this->contentType;
        return $this;
    }

    /** @return string gets the content */
    public function getContent() { return $this->content; }


    /** Executes the response, setting the current page's response code & headers, echoing out the contents and then exiting. */
    public function respond() {
        
        //Cookies
        HTTP::applyCookies();

        //Set the status code
        http_response_code($this->status);
        
        //Update the content type
        $this->headers['content-type'] = $this->headers['content-type'] ?? $this->contentType;

        //Add all the headers
        foreach($this->headers as $key => $pair) {
            header($pair == null ? $key : $key . ": " . $pair);
        }

        //respond the data
        if ($this->contentType == HTTP::CONTENT_APPLICATION_JSON) {
            echo json_encode($this->content);
        } else {
            echo $this->content;
        }

        //Die
        exit;
    }
}