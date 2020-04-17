<?php
namespace widget;

use helpers\HTML;

class Breadcrumb extends Widget {

    private static $breadcrumb = [];
    
    public static function add($title, $url) {
        self::$breadcrumb[] = [ 'title' => $title, 'url' => HTML::href($url) ];
    }

    public static function count() { return count(self::$breadcrumb); }
    
    /** {@inheritdoc} */
    public function begin() {
        echo '<nav class="breadcrumb" aria-label="breadcrumbs"><ul>';
            for($i = 0; $i < count(self::$breadcrumb); $i++) {
                $crumb = self::$breadcrumb[$i];
                if ($i == count(self::$breadcrumb) - 1)
                    echo "<li class='is-active'><a href='{$crumb['url']}'>{$crumb['title']}</a></li>";
                else
                    echo "<li><a href='{$crumb['url']}'>{$crumb['title']}</a></li>";
            }
        echo '</ul></nav>';

    }

    /** {@inheritdoc} */
    public function end() {

    }

}