
<?php

use kiss\controllers\Controller;
use kiss\helpers\HTML;
use widget\Breadcrumb;
use widget\Menu;
use widget\Notification;

?>

<body>
    <?= $this->renderContent('@/views/base/navigation') ?>
    <div class="container">        
        <div class="columns"> 
            <?php if (Breadcrumb::count() > 0): ?>
                    <div class="column is-3 ">
                        <?= Menu::widget(); ?>
                    </div>
                    <div class="column is-9">
                        <?= Breadcrumb::widget(); ?>
                        <?= Notification::widget(); ?>
                        <?= $_VIEW; ?>
                    </div>
            <?php else: ?>
                <div class="column is-12">
                    <?= Notification::widget(); ?>
                    <?= $_VIEW; ?>
                </div>
            <?php endif;?>
        </div>
    </div>
    

    <footer class="footer">
        <div class="content has-text-centered">
            <div class="level">
                <div class="level-item">
                    <a href="https://jwt.io" target="_blank"><img src="http://jwt.io/img/badge-compatible.svg"></a>
                </div>
                <div class="level-item">
                    im a potato
                </div>

            </div>
        </div>
    </footer>
</body>


    