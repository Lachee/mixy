<?php
use db\Connection;
use helpers\HTTP;
use helpers\Response;
use core\models\Session;
use models\xve\XVEAdapter;
use models\xve\XVEConfigurator;
use xve\configuration\Configurator;
use xve\configuration\FileConfigurator;

class App {

    /** @var App The application */
    public static $xve;

    /** @var Session The current session ID */
    public $session;

    /** @var Connection The connection to the database  */
    protected  $connection;

    /** @var Predis\Client redis client */
    protected $redis;

    private $config;
    private $defaultResponseType = HTTP::CONTENT_TEXT_HTML;

    /** @var Configurator the XVE configurator */
    private $xveConfigurator = null;

    function __construct($config)
    {
        self::$xve = $this;
        $this->config = $config;
        $this->connection = new Connection($config['db']['dsn'], $config['db']['user'], $config['db']['pass'], array(), $config['db']['prefix']);
        $this->redis = new Predis\Client([], ['prefix' => 'XVE:']);

        if (!defined("NO_SESSION")) {
            $this->session = new Session();
            $this->session->start();
        } else {
            $this->session = null;
        }
    }

    function db() : Connection {
        return $this->connection;
    }

    /** Gets the current XVE. If it does not exist then one will be made and loaded */
    function getXveConfigurator() : Configurator {
        if ($this->xveConfigurator == null) {
            //$this->xveConfigurator = new FileConfigurator();
            $this->xveConfigurator = new XVEConfigurator();
            $this->xveConfigurator->setDb(new XVEAdapter([ 'app' => $this, 'connection' => $this->connection ]));
            $this->xveConfigurator->load();
        }
        return $this->xveConfigurator;
    }

    function redis() : Predis\Client {
        return $this->redis;
    }

    /** Sets the default response type */
    function setDefaultResponseType($type) {
        $this->defaultResponseType = $type;
        return $this;
    }

    /** Gets the current default response type. This can be used to determine how we should respond */
    function getDefaultResponseType() { return $this->defaultResponseType; }

    /** The base directory */
    function baseDir() { return __DIR__; }

    function baseURL() { return $this->config['baseURL'] ?? sprintf( "%s://%s%s",
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
} 