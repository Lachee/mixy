<?php
use helpers\HTML;
?>

<script src="/dist/bundle.emojipicker.js"></script>

<section class="hero hero is-gradient is-primary">
    <div class="hero-body">
        <div class="container">
            <h1 class="title"><?= $title ?></h1>
            <h2 class="subtitle">
                I hope you are having a great day!
            </h2>
        </div>
    </div>
</section>
<button id="my-icon">Icon Picker</button>
<input type="text" id="my-input" />

<script>

    const container = emoji.createContainer();
    const icon      = document.getElementById('my-icon');
    const editable  = document.getElementById('my-input');

    document.querySelector('body').prepend(container);

    (async () => {
        console.log("Creating Emoji Picker...");
        let picker = await emoji.createEmojiPickerAsync({
            sheets: {
                apple   : '/dist/sheets/sheet_apple_64_indexed_128.png',
                google  : '/dist/sheets/sheet_google_64_indexed_128.png',
                twitter : '/dist/sheets/sheet_twitter_64_indexed_128.png',
                emojione: '/dist/sheets/sheet_emojione_64_indexed_128.png'
            },
            categories: [
                {
                    title: "People",
                    icon : '<i class="far fa-user" aria-hidden="true"></i>'
                },
                {
                    title: "Nature",
                    icon : '<i class="far fa-leaf" aria-hidden="true"></i>'
                },
                {
                    title: "Foods",
                    icon : '<i class="far fa-fish-cooked" aria-hidden="true"></i>'
                },
                {
                    title: "Activity",
                    icon : '<i class="far fa-futbol" aria-hidden="true"></i>'
                },
                {
                    title: "Places",
                    icon : '<i class="far fa-city" aria-hidden="true"></i>'
                },
                {
                    title: "Symbols",
                    icon : '<i class="far fa-icons" aria-hidden="true"></i>'
                },
                {
                    title: "Flags",
                    icon : '<i class="far fa-flag-checkered" aria-hidden="true"></i>'
                }
            ]
        });

        console.log("Created!");
        picker.listenOn(icon, container, editable);
    })();

</script>