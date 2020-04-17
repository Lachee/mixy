<?php

namespace kiss\models;

use kiss\exception\InvalidOperationException;
use kiss\helpers\HTML;

class Session extends BaseObject {
    const KEY_NOTIFICATIONS = '_notifications';

    protected $id;

    /** Gets the current session ID.
     * @return string|null the session id, null if there is none available.
     */
    public function getId() {
        return $this->id;
    }

    /** Gets a session value */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /** Sets a session key  */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /** Checks if a session key is set */
    public function isset($key) {
        return isset($_SESSION[$key]);
    }

    /** starts a session.
     * @return string session id.
     */
    public function start() {
        if (!session_start())
            throw new InvalidOperationException();
        return $this->id = session_id();
    }

    /** stops the current session 
     * @return string previous session id.
    */
    public function stop() {
        if (!session_abort())
            throw new InvalidOperationException();
        return $this->id;
    }

    /** Destroys session data entirely */
    public function abort() {
        session_destroy();
        $this->id = null;
    }

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