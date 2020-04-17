<?php

namespace kiss\models;

use kiss\helpers\HTML;

class Session extends BaseObject {

    const KEY_NOTIFICATIONS = '_notifications';

    public $id;

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function isset($key) {
        return isset($_SESSION[$key]);
    }

    public function start() {
        session_start();
        $this->id = session_id();
    }

    public function stop() {
        session_abort();
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