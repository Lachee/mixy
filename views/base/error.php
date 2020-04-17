<?php

use kiss\exception\HttpException;
use kiss\helpers\HTML;
use kiss\helpers\HTTP;

?>



<style>
    section.bulma404 {
        position: fixed;
        bottom: 0;
        width: 34%;
        right: 10%;
    }
</style>

<section class="notification hero has-gradient is-danger welcome is-small">
    <div class="hero-body">
        <div class="container">
            <h1 class="title">Woops!</h1>
            <h2 class="subtitle">
                Something went wrong while trying to serve you that page.
            </h2>
        </div>
    </div>
</section>

<section  class="section">
    <div class="container">
        <div class="columns">
            <div class='column is-one-third'>
            
                <div class="card">
                    <div class="card-content">
                        <p class="title">HTTP <?= $exception->getStatus() ?></p>
                        <p class="subtitle"><?= HTTP::status($exception->getStatus()); ?> </p>                        
                        <?= $exception->getMessage(); ?>
                    </div>
                    <footer class="card-footer">
                        <p class="card-footer-item">
                        <span>  Go <a onclick="window.history.back();">Back</a> </span>
                        </p>
                        <p class="card-footer-item">
                        <span> View on <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/<?= $exception->getStatus() ?>">MDN</a></span>
                        </p>
                    </footer>
                </div>
            </div>
            <div class='column is-two-thirds'>                
                <?php if($exception->getStatus() == 500): ?>
                    <?= var_dump($exception); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="bulma404">
    <img src="images/bulma.png">
</section>