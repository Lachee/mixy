<?php
use kiss\helpers\HTML;
?>

<div class="columns">
    <div class="column" id="monaco-column">
            <div class="tabs is-small">
            <ul>
                <li data-lang="html" ><a>HTML</a></li>
                <li data-lang="js" class="is-active"><a>JavaScript</a></li>
                <li data-lang="css" ><a>CSS</a></li>
            </ul>
            </div>
        <div id="monaco-editor" class="container" style="height:600px">
        </div>
    </div>

    <div class="column">
        potato
    </div>
</div>

<script src="./dist/monaco/main.js"></script>