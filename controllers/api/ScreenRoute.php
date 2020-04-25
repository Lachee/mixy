<?php namespace app\controllers\api;

use app\models\Screen;
use kiss\exception\HttpException;
use kiss\helpers\HTTP;
use kiss\router\Route;
use kiss\router\RouteFactory;

class ScreenRoute extends Route {

    public $uuid;

    //We are going to return our routing. Any segment that starts with : is a property.
    // Note that more explicit routes get higher priority. So /example/apple will take priority over /example/:fish
    public static function getRouting() { return "/screen/:uuid"; }

    //HTTP GET on the route. Return an object and it will be sent back as JSON to the client.
    // Throw an exception to send exceptions back.
    // Supports get, delete
    public function get() {
        return $this->getScreen();
    }

    /** update a record */
    public function put() {
        $body = HTTP::json();
        $screen = $this->getScreen();

        if(isset($body['html']))
            $screen->html = $body['html'];
        if(isset($body['js']))
            $screen->js = $body['js'];
        if(isset($body['css']))
            $screen->css = $body['css'];
        if(isset($body['json']))
            $screen->json = $body['json'];
        
        $screen->save();
        return $screen;
    }

    /** Gets the schema */
    public function options(){ 
        return Screen::getJsonSchema();
    }

    /** @return Screen */
    private function getScreen() {
        $query = Screen::findByUuid($this->uuid);
        $model = $query->one();
        if ($model == null) throw new HttpException(HTTP::NOT_FOUND);
        return $model;
    }
}