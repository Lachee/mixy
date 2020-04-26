<?php
use kiss\helpers\HTML;
use kiss\Kiss;

?>
<html>
    <head>
        <title><?= HTML::$title ?></title>
        <base href="<?= Kiss::$app->baseURL()?>">
        
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        
        <!-- JQuery -->
        <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>

        <!-- Bulma Version 0.8.x--> 
        <!--<link rel="stylesheet" href="https://unpkg.com/bulma@0.8.0/css/bulma.min.css" />-->
        <!--https://jenil.github.io/bulmaswatch/-->
        <!--<?php if (!empty($theme)) ?> <link rel="stylesheet" href="https://unpkg.com/bulmaswatch/<?= $theme ?>/bulmaswatch.min.css">-->

        <!-- JSON Form -->
        <script src="https://cdn.jsdelivr.net/npm/@json-editor/json-editor@latest/dist/jsoneditor.min.js"></script>

        <!-- Webpacks -->
        <!--
        <script src="/dist/kiss.js"></script>
        <link rel="stylesheet" href="/dist/kiss.css">
        -->
        
        <!-- Dependencies -->
        <script src="/dist/mixy.js"></script>
        <script src="https://code.createjs.com/1.0.0/easeljs.min.js"></script>
        <script src="https://code.createjs.com/1.0.0/tweenjs.min.js"></script>

        <!-- App -->
        <!--
            <script src="/dist/app.js"></script>
            <link rel="stylesheet" href="/dist/app.css">
        -->
        <script>
            window._alert = window.alert;
            window.alert = function(alert) { console.log("ALERT", alert); };
        </script>
        <style>
            body { 
                background: transparent; 
                padding: 0;
                margin: 0; 
                overflow: hidden; 
                width: 1920px;
                height: 1080px;
            }
            <?= $css ?>
        </style>
    </head>
    <body>
        <?= $html ?>
    </body>
    <script>
        let options = <?= json_encode($json) ?>;
        (function() { <?= $js ?> })();
    </script>
</html>