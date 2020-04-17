
<?php

use helpers\HTML;
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

<script async type="text/javascript" src="js/bulma.js"></script>
</body>
