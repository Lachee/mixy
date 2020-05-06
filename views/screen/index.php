<?php
use kiss\helpers\HTML;
use kiss\Kiss;

?>

<h2>Token URL:</h2>
<input type="text" class="input is-secret" value="<?= $viewUrl ?>"/>

<h2>Editor Form</h2>
<form method="POST">
    <div id="json-editor"></div>
    <button class="button is-primary is-outlined">Save</button>
</form>
<script>
    $(document).ready(() => {
        app.viewPromise.then(v => {
            app.view.createSchemaEditor({
                theme: 'spectre',    
                iconlib: "fontawesome5",
                disable_collapse: true,
                no_additional_properties: true,
                //disable_properties: true,
                disable_edit_json: true,
                disable_collapse: true,
                compact: true,
                object_layout: 'table',
                value: <?= json_encode($configData); ?>,
                default: <?= json_encode($defaultData); ?>
            });
        });
    });
</script>
