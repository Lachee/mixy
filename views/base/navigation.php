<?php

use kiss\helpers\HTML;
use kiss\Kiss;
$user = Mixy::$app->getUser()
?>
<!-- START NAV -->
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item brand-text" href="<?= HTML::href('/')?>">Mixy</a>
            <div class="navbar-burger burger" data-target="navMenu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <div id="navMenu" class="navbar-menu">
            <div class="navbar-start">
                <?php if ($user): ?>
                    <a class="navbar-item" href="<?= HTML::href('/screen/')?>">Screens</a>
                    <a class="navbar-item" href="<?= HTML::href('/editor/')?>">Editor</a>
                    <!--<a class="navbar-item" href="admin.html">Exceptions</a>-->
                    <a class="navbar-item" href="<?= HTML::href('/account/')?>">Account</a>
                <?php endif;  ?>
            </div>
        </div>
        <div id="navMenu" class="navbar-end">
            <div class="navbar-start">
                <div class="navbar-item">
                    <?php if ($user): ?>
                        <div class="field has-addons"> 
                            <p class="control">
                                <a class="button" id="login-button" href="https://mixer.com/<?= HTML::encode($user->username); ?>" >
                                    <span class="icon"><i class="fab fa-mixer"></i></span>
                                    <span><?= HTML::encode($user->username) ?></span>
                                </a>
                            </p>
                            <p class="control">
                                <a class="button" href="<?= HTML::href(['/logout']); ?>">
                                    <span class="icon"><i class="far fa-sign-out"></i></span>
                                </a>
                            </p>
                        </div>
                    <?php else: ?>
                        <p class="control">
                            <a class="button" id="login-button">
                                <span class="icon"><i class="fab fa-mixer"></i></span>
                                <span>Login</span>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- END NAV -->