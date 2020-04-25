<?php
use kiss\helpers\HTML;
$fullWidth = true;
?>
<div class="row">
    <div id="monaco-tabs" class="tabs is-small">
        <ul>
            <li data-lang="html" ><a>HTML</a></li>
            <li data-lang="js" class="is-active"><a>JavaScript</a></li>
            <li data-lang="css" ><a>CSS</a></li>
            <li data-lang="json" ><a>Default Settings</a></li>
        </ul>
    </div>
</div>
<div class="row">
<div class="panes"> 

    <div class="pane pane-left" id="monaco-column">
           
        <div id="monaco-editor" class="container" style="height:600px">
        </div>
    </div>

    <div class="pane pane-right">
        <div class="iframe-wrapper">
            <iframe src="" class="preview" sandbox="allow-scripts" width="1920px" height="1080px"></iframe>
        </div>
    </div>
</div>
</div>

<script src="./dist/monaco/main.js"></script>