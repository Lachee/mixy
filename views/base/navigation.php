<?php

use kiss\helpers\HTML;

?>
<!-- START NAV -->
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item brand-text" href="<?= HTML::href('/')?>">XVE Bot Creator</a>
            <div class="navbar-burger burger" data-target="navMenu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <div id="navMenu" class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="<?= HTML::href('/')?>">Home</a>
                <a class="navbar-item" href="<?= HTML::href('/projects/')?>">Projects</a>
                <a class="navbar-item" href="<?= HTML::href('/editor/100/')?>">Editor</a>
                <!--<a class="navbar-item" href="admin.html">Exceptions</a>-->
                <a class="navbar-item" href="<?= HTML::href('/manager/')?>">Services</a>
            </div>
        </div>
    </div>
</nav>
<!-- END NAV -->