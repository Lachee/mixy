<?php
use kiss\helpers\HTML;

?>

<div id="json-editor"></div>
<script>
    $(document).ready(() => {
        app.viewPromise.then(v => {
            app.view.createSchemaEditor({
                theme: 'spectre',    
                iconlib: "fontawesome5",
                disable_collapse: true,
                no_additional_properties: true,
                value: <?= json_encode($config->getJson()); ?>,
                default: <?= json_encode($screen->getJson()); ?>
            });
        });
    });
</script>
