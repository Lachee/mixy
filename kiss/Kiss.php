<?php namespace kiss;

if (!defined('KISS_AUTOLOAD_DIR'))
    define('KISS_AUTOLOAD_DIR', __DIR__ . '/../');

if (!defined('KISS_SESSIONLESS'))
    define('KISS_SESSIONLESS', false);

use Exception;
use kiss\db\Connection;
use kiss\helpers\HTTP;
use kiss\helpers\Response;
use kiss\models\BaseObject;
use kiss\models\Session;

/** Base application */
class Kiss extends BaseObject {
    
    /** @var Kiss static instance of the current application */
    public static $app;

    /** @var string the base URL */
    protected $baseUrl;

    /** @var string the base namespace */
    protected $baseNamespace = 'app';

    /** @var string main controller */
    public $mainController = 'app\\controllers\\MainController';

    /** @var Session current session object */
    public $session = [ '$class' => Session::class ];

    /** @var BaseObject[] collection of components */
    protected $components = [];

    /** @var Connection the database */
    protected $db = null;

    /** @var ICache the current cache */
    protected $cache = null;

    public function __construct($options = []) {
        Kiss::$app = $this;
        parent::__construct($options);
    }

    protected function init() {
        //Prepare the DB
        if ($this->db != null) {
            $this->db = new Connection($this->db['dsn'],$this->db['user'],$this->db['pass'], array(), $this->db['prefix']);
        }

        if (KISS_SESSIONLESS) {
            $this->session = null;
        } else {
            $this->initializeObject($this->session);
            $this->session->start();
        }
    }

    /** @return Connection the current database. */
    public function db() { 
        return $this->db;
    }

    /** @return ICache the current cache. */
    public function cache() {
        return $this->cache;
    }

    /** magic get value */
    public function __get($name) {
        if (isset($this->components[$name]))
            return $this->components[$name];
        return parent::__get($name);
    }

    /** @var string default response type */
    private $defaultResponseType = 'text/html';

    /** Gets the current default response type. This can be used to determine how we should respond */
    function getDefaultResponseType() { return $this->defaultResponseType; }
    /** Sets the current defualt response type. */
    function setDefaultResponseType($type) { $this->defaultResponseType = $type; return $this; }

    /** Gets teh current base namespace */
    function getBaseNamespace() { return $this->baseNamespace; }

    /** The base directory
     * @return string
    */
    function baseDir() { return KISS_AUTOLOAD_DIR; }
    
    /** The base URL 
     * @return string
     */
    function baseURL() { return $this->baseUrl ?? sprintf( "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
      );
     }

    /** Responds based on the respons mode */
    function respond($response, $status = HTTP::OK) {
        //Prepare the response if it isn't already a Response object.
        if (!($response instanceof Response)) {

            //If the response is an exception, then make it an exception response
            if ($response instanceof Exception) {                
                $response = Response::exception($response);
            } else {

                //Its just a payload, so check if the payload is raw contents or if it should be encoded as a JSON payload.
                if ($this->defaultResponseType == HTTP::CONTENT_APPLICATION_JSON) {
                    //Prepare a json default response
                    $response = Response::json($status, $response);

                } else {

                    //Prepare a regular default response
                    $response = new Response($status, [], $response, $this->defaultResponseType);
                }

            }

        }

        //Return the response
        $response->respond();
    }

    /** Creates an object, alias of BaseObject::create */
    public static function create($class, $properties = []) {
        return BaseObject::create($class, $properties);
    }
}

//Setup an Alias of 'K'
class_alias(Kiss::class, 'kiss\\K');