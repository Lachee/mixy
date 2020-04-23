import { MonacoEditor } from "../monaco/index";


$(document).ready(async () => {
    
    //Load the data
    let loadedScreenResponse = await fetch('/api/screen/24f067c8-853a-11ea-bc55-0242ac130003', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
    });

    const loadedScreenData = await loadedScreenResponse.json();
    const container = $("#monaco-editor").get(0);
    const editor = new MonacoEditor(container);
    editor.setValues({ html: loadedScreenData.data.html, js: loadedScreenData.data.js, css: loadedScreenData.data.css });
    
    editor.on("languageChanged", (language, model) => {
        console.log(language, model);
        $("#monaco-column .tabs li").removeClass("is-active");
        $("#monaco-column .tabs li[data-lang="+language+"]").addClass("is-active");
    });

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
    });

    editor.on("run", () => {
        //TODO: refresh the stuff
    });


    $("#monaco-column .tabs a").click((data) => {
        let $parent = $(data.target).parent();
        editor.setLanguage($parent.data("lang"));
    });    
});