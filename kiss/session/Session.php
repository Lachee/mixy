<?php

namespace kiss\session;

use kiss\exception\ArgumentException;
use kiss\exception\InvalidOperationException;
use kiss\helpers\HTML;
use kiss\helpers\HTTP;
use kiss\Kiss;
use kiss\models\BaseObject;

abstract class Session extends BaseObject {

    /** @var string the cookie name */
    public const JWT_COOKIE_NAME = '_KISSJWT';
    
    /** @var string the name of the notification session */
    private const KEY_NOTIFICATIONS = '$notifications';

    /** @var string current session id */
    private $session_id;

    /** @var string the current JWT */
    private $jwt;

    /** @var object session tokens from the JWT */
    private $tokens = null;

    /** @var int how long sessions last for in seconds by default. */
    public $sessionDuration = 24*60*60;

    /** Initializes the session from JWT. Throws if unable. */
    protected function init() {
        try { 
            $jwt = HTTP::cookie(self::JWT_COOKIE_NAME, null);
            $this->setJWT($jwt, false);
        }catch(ArgumentException $e) { }
    }

    /** @return string the current JWT */
    public function getJWT() { return $this->jwt; }

    /** @return object the current JWT tokens. */
    public function getTokens() { return $this->tokens; }

    /** Sets the current JWT. If the session details reset then the current session will be aborted. */
    public function setJWT($jwt, $destroySession = true) {
        if ($jwt == null) throw new ArgumentException('No valid JWT');
        $this->jwt = $jwt;

        $this->tokens = Kiss::$app->jwtProvider->decode($this->jwt);
        if (empty($this->tokens->sid)) throw new InvalidOperationException("Invalid Token");

        $previousSessionId = $this->session_id;
        $this->session_id = $this->tokens->sid;

        //Start-Stop because there is a difference in id
        if ($destroySession && $previousSessionId != $this->session_id) 
            $this->reset()->start();
        

        //Store the JWT
        HTTP::setCookie(self::JWT_COOKIE_NAME, $this->jwt, $this->tokens->exp);
    }

    /** Gets the current session ID.
     * @return string|null the session id, null if there is none available.
     */
    public function getSessionId() {
        return $this->session_id;
    }

    /** Sets the current session ID */
    protected function setSessionId($sid) {

        //set the SID. Doing so now will prevent us needless restarting the session
        $this->session_id = $sid;

        if ($sid == null) 
        {
            //Clear the cookie
            $this->tokens   = null;
            $this->jwt      = null;
            HTTP::setCookie(self::JWT_COOKIE_NAME, '', 10);
        } 
        else 
        {
            //Update what our JTW is
            $jwt = Kiss::$app->jwtProvider->encode([ 'sid' => $sid ], $this->sessionDuration);
            $this->setJWT($jwt);
        }
        return $this;
    }


    /** Creates a session, or resumes an existing one.
     * @return Session this
     */
    public abstract function start();

    /** Finishes the session without saving any pending data. Behalves like session_abort.
     * @return Session this
     */
    public abstract function reset();

    /** Destroys a session and clears all its data.
     * @return Session this
    */
    public abstract function stop();

    /** Gets a session value 
     * @param string $key the key in the session
     * @param mixed $default the default value to return
     * @return mixed the value, otherwise default.
    */
    public abstract function get($key, $default = null);
    
    /** Sets a session key
     * @param string $key the key in the session
     * @param mixed $value the value to store. Not every implementation of session may support complex objects, so it is recommended to only store simple strings or hashmaps.
     * @return bool true if setting was sucessful.
      */
    public abstract function set($key, $value);

    /** Checks if a session key is set
     * @param string $key the key in the session
     * @return bool true if it is set
     */
    public abstract function isset($key);



    /** Adds a notification.
     * @param string|string[] notifications
     */
    public function addNotification($notification, $type = 'info') {
        if (!is_array($notification)) { $notification = [$notification]; }

        foreach($notification as $notif) {
            $notifications = $this->get(self::KEY_NOTIFICATIONS, []);
            $notifications[] = [ 'content' => HTML::encode($notif), 'raw' => $notif, 'type' => $type ];
            $this->set(self::KEY_NOTIFICATIONS, $notifications);
        }
    }

    /** Fetches all notifications and clears the list */
    public function consumeNotifications() {
        $notifications = $this->get(self::KEY_NOTIFICATIONS, []);
        $this->set(self::KEY_NOTIFICATIONS, []);
        return $notifications;
    }
}