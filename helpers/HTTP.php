<?php
namespace helpers;
class HTTP {
    const CONTINUE = 100;
    const SWITCHING_PROTOCOLS = 101;
    const PROCESSING = 102;            // RFC2518
    const EARLY_HINTS = 103;           // RFC8297
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTI_STATUS = 207;          // RFC4918
    const ALREADY_REPORTED = 208;      // RFC5842
    const IM_USED = 226;               // RFC3229
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const RESERVED = 306;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENTLY_REDIRECT = 308;  // RFC7238
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const I_AM_A_TEAPOT = 418;                                               // RFC2324
    const MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const LOCKED = 423;                                                      // RFC4918
    const FAILED_DEPENDENCY = 424;                                           // RFC4918
    const TOO_EARLY = 425;                                                   // RFC-ietf-httpbis-replay-04
    const UPGRADE_REQUIRED = 426;                                            // RFC2817
    const PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const VERSION_NOT_SUPPORTED = 505;
    const VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const LOOP_DETECTED = 508;                                               // RFC5842
    const NOT_EXTENDED = 510;                                                // RFC2774
    const NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    const CONTENT_TEXT_HTML = 'text/html';
    const CONTENT_TEXT_PLAIN = 'text/plain';
    const CONTENT_APPLICATION_JSON = 'application/json';
    const CONTENT_APPLICATION_JAVASCRIPT = 'application/javascript';
    const CONTENT_APPLICATION_OCTET_STREAM = 'application/octet-stream';

    private static $statusMessages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot', // RFC 2324
        419 => 'Authentication Timeout', // not in RFC 2616
        420 => 'Method Failure', // Spring Framework
        420 => 'Enhance Your Calm', // Twitter
        422 => 'Unprocessable Entity', // WebDAV; RFC 4918
        423 => 'Locked', // WebDAV; RFC 4918
        424 => 'Failed Dependency', // WebDAV; RFC 4918
        424 => 'Method Failure', // WebDAV)
        425 => 'Unordered Collection', // Internet draft
        426 => 'Upgrade Required', // RFC 2817
        428 => 'Precondition Required', // RFC 6585
        429 => 'Too Many Requests', // RFC 6585
        431 => 'Request Header Fields Too Large', // RFC 6585
        444 => 'No Response', // Nginx
        449 => 'Retry With', // Microsoft
        450 => 'Blocked by Windows Parental Controls', // Microsoft
        451 => 'Unavailable For Legal Reasons', // Internet draft
        451 => 'Redirect', // Microsoft
        494 => 'Request Header Too Large', // Nginx
        495 => 'Cert Error', // Nginx
        496 => 'No Cert', // Nginx
        497 => 'HTTP to HTTPS', // Nginx
        499 => 'Client Closed Request', // Nginx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates', // RFC 2295
        507 => 'Insufficient Storage', // WebDAV; RFC 4918
        508 => 'Loop Detected', // WebDAV; RFC 5842
        509 => 'Bandwidth Limit Exceeded', // Apache bw/limited extension
        510 => 'Not Extended', // RFC 2774
        511 => 'Network Authentication Required', // RFC 6585
        598 => 'Network read timeout error', // Unknown
        599 => 'Network connect timeout error', // Unknown
    ];


    /** @return string the status message associated with the code */
    public static function status($code) {
        if (isset(self::$statusMessages[$code])) return self::$statusMessages[$code];
        return 'Unkown Exception';
    }

    /** Gets the current route */
    public static function route() {
        //Prepare the route we wish to use 
        $route = $_REQUEST['route'] ?? '';
         
        if (empty($route) && !empty($_SERVER['REDIRECT_URL']))
            $route = $_SERVER['REDIRECT_URL'];

        if (empty($route)) 
            $route = "/";

        return $route;
    }

    /** Gets the current host */
    public static function host() {
        return $_SERVER['HTTP_HOST'];
    }

    /** Get query parameters passed with the request */
    public static function get($variable, $default = null, $filter = null) {
        if ($filter == FILTER_VALIDATE_BOOLEAN && isset($_GET[$variable]) && empty($_GET[$variable])) {
            $_GET[$variable] = $default;
        }

        $val = $_GET[$variable] ?? $default;
        if ($filter == null) return $val;

        $result = filter_var($val, $filter, FILTER_NULL_ON_FAILURE);
        return $result !== null ? $result : $default;
    }

    /** Post query parameters passed with the request */
    public static function post($variable, $default = null, $filter = null) {
        if ($filter == null) return $_POST[$variable] ?? $default;
        $result = filter_var($_POST[$variable] ?? $default, $filter);
        return $result !== false ? $result : $default;
    }    
    
    /** @return bool if the doc has post. */
    public static function hasPost(){ return isset($_POST) && count($_POST) > 0; }

    /** An query paramaters passed with the request */
    public static function request($variable, $default = null, $filter = null) {
        if ($filter == null) return $_REQUEST[$variable] ?? $default;
        $result = filter_var($_REQUEST[$variable] ?? $default, $filter);
        return $result !== false ? $result : $default;
    }

    /** Gets a header value. */
    public static function header($variable, $default = null, $filter = null) {
        $_HEADERS = self::headers();

        if ($filter == null) return $_HEADERS[$variable] ?? $default;
        $result = filter_var($_HEADERS[$variable] ?? $default, $filter);
        return $result !== false ? $result : $default;
    }

    /** Gets all the headers */
    public static function headers() { return getallheaders(); }


    /** The body supplied with the request */
    public static function body() {
        return file_get_contents('php://input');
    }

    /** The json supplied in the request body, as an associative array. */
    public static function json() {
        return json_decode(self::body(), true);
    }
}