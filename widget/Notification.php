<?php
namespace widget;

use App;
use helpers\HTML;

class Notification extends Widget {

    
    /** {@inheritdoc} */
    public function begin() {
        $notifications = App::$xve->session->consumeNotifications();
        echo '<section class="notifications content">';
        foreach($notifications as $notification) {
            $content = $notification['content'];
            $type = $notification['type'];
            if (isset($notification['html']) && $notification['html'] == true) 
                $content = $notification['raw'];

            echo "<div class='notification is-{$type}'>";
            echo '<button class="delete"></button>';
            echo $content;
            echo "</div>";
        }
        echo '</section>';
    }

}