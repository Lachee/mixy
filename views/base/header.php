<?php
use kiss\helpers\HTML;
use kiss\helpers\HTTP;
use kiss\Kiss;

$theme = HTTP::get('theme', 'lumen');
?>
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

    <!-- selectize -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css" integrity="sha256-EhmqrzYSImS7269rfDxk4H+AHDyu/KwV1d8FDgIXScI=" crossorigin="anonymous" />

    <!-- Bulma Version 0.8.x--> 
    <!--<link rel="stylesheet" href="https://unpkg.com/bulma@0.8.0/css/bulma.min.css" />-->
    <!--https://jenil.github.io/bulmaswatch/-->
    <!--<?php if (!empty($theme)) ?> <link rel="stylesheet" href="https://unpkg.com/bulmaswatch/<?= $theme ?>/bulmaswatch.min.css">-->

    <!-- JSON Form -->
    <script src="https://cdn.jsdelivr.net/npm/@json-editor/json-editor@latest/dist/jsoneditor.min.js"></script>

    <!-- Webpacks -->
    <script src="/dist/bundle.app.js"></script>
    <link rel="stylesheet" href="/dist/bundle.app.css">
</head>