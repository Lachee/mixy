<?php

use kiss\helpers\HTML;
use kiss\Kiss;

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
        <div id="navMenu" class="navbar-end">
            <div class="navbar-start">
                <div class="navbar-item">
                    <p class="control">
                        <?php if (($user = Kiss::$app->mixer->debugGetUser()) != null): ?>
                            <a class="button" id="login-button" href="https://mixer.com/<?= HTML::encode($user->channel['token']); ?>" >
                                <span class="icon"><i class="fab fa-mixer"></i></span>
                                <span><?= $user->username ?></span>
                            </a>
                        <?php else: ?>
                            <a class="button" id="login-button">
                                <span class="icon"><i class="fab fa-mixer"></i></span>
                                <span>Login</span>
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- END NAV -->