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
use kiss\models\JWTProvider;
use kiss\schema\RefProperty;
use kiss\session\Session;
use kiss\session\PhpSession;

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

    /** @var \Predis\Client the current redis instance */
    protected $redis = null;

    /** Node Provider for uuids. */
    public $uuidNodeProvider =  null;

    /** @var Connection the database */
    protected $db = null;
    
    /** @var string default response type */
    private $defaultResponseType = 'text/html';

    /** @var BaseObject[] collection of components */
    protected $components = [];

    /** @var JWTProvider the JWT provider. */
    public $jwtProvider = [ '$class' => JWTProvider::class ];

    /** @var Session current session object */
    public $session = [ '$class' => PhpSession::class ];


    /** {@inheritdoc} */
    public static function getSchemaProperties($options = []) {
        return array_merge(parent::getSchemaProperties($options), [
            'jwtProvider' => new RefProperty(JWTProvider::class)
        ]);
    }

    public function __construct($options = []) {
        Kiss::$app = $this;
        parent::__construct($options);
    }

    protected function init() {
        if ($this->uuidNodeProvider == null)
            $this->uuidNodeProvider = new \Ramsey\Uuid\Provider\Node\RandomNodeProvider();
        
        if ($this->redis == null)
            $this->redis = new \Predis\Client();
            
        if ($this->db != null) 
            $this->db = new Connection($this->db['dsn'],$this->db['user'],$this->db['pass'], array(), $this->db['prefix']);
        
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

    /** @return \Predis\Client the current redis client */
    public function redis() {
        return $this->redis;
    }

    /** magic get value */
    public function __get($name) {
        if (isset($this->components[$name]))
            return $this->components[$name];
    }

    /** Gets the current default response type. This can be used to determine how we should respond */
    public function getDefaultResponseType() { return $this->defaultResponseType; }
    /** Sets the current defualt response type. */
    public function setDefaultResponseType($type) { $this->defaultResponseType = $type; return $this; }

    /** Gets teh current base namespace */
    public function getBaseNamespace() { return $this->baseNamespace; }

    /** The base directory
     * @return string
    */
    public function baseDir() { return KISS_AUTOLOAD_DIR; }
    
    /** The base URL 
     * @return string
     */
    public function baseURL() { return $this->baseUrl ?? sprintf( "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
      );
     }

    /** Responds based on the respons mode */
    public function respond($response, $status = HTTP::OK) {
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
        exit;
    }

}

//Setup an Alias of 'K'
class_alias(Kiss::class, 'kiss\\K');