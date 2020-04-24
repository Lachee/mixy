import { MonacoEditor } from "../monaco/index";


$(document).ready(async () => {
    
    //Load the data
    let loadedScreenResponse = await fetch('/api/screen/24f067c8-853a-11ea-bc55-0242ac130003', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
    });

    //Get the JSON data
    const loadedScreenData = await loadedScreenResponse.json();
    const container = $("#monaco-editor").get(0);
    const editor = new MonacoEditor(container);
    editor.setValues({ html: loadedScreenData.data.html, js: loadedScreenData.data.js, css: loadedScreenData.data.css });
    
    //refresh the loader
    refreshPreview();

    //Update the tabs
    editor.on("languageChanged", (language, model) => {
        console.log(language, model);
        $("#monaco-tabs.tabs li").removeClass("is-active");
        $("#monaco-tabs.tabs li[data-lang="+language+"]").addClass("is-active");
    });

    //Save the document
    editor.on("save", async (models) => {
        console.log("save models", models);

        let content = {
            html: models.html.getValue(),
            css: models.css.getValue(),
            js: models.js.getValue(),
        }

        await fetch('/api/screen/24f067c8-853a-11ea-bc55-0242ac130003', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(content)
        });

        refreshPreview();
    });

    editor.on("run", () => {
    });

    $("#monaco-tabs.tabs a").click((data) => {
        console.log("click");
        let $parent = $(data.target).parent();
        editor.setLanguage($parent.data("lang"));
    });
    

    function refreshPreview() {
        $(".iframe-wrapper .preview").fadeOut();
        $(".iframe-wrapper .preview").get(0).src = '/player/24f067c8-853a-11ea-bc55-0242ac130003/';
    }
});