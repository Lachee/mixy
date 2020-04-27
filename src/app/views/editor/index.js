import './index.scss';
import { MonacoEditor } from "monaco/index";
import  GenerateSchema   from 'generate-schema';



$(document).ready(async () => {
    const $iframe = $(".iframe-wrapper .preview");
    const $monaco = $("#monaco-editor");

    //Load the data
    let loadedScreenResponse = await fetch('/api/screen/24f067c8-853a-11ea-bc55-0242ac130003', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
    });

    //Get the JSON data
    const loadedScreenData = await loadedScreenResponse.json();
    const container = $monaco.get(0);
    const editor = new MonacoEditor(container);
    editor.setValues({ html: loadedScreenData.data.html, js: loadedScreenData.data.js, css: loadedScreenData.data.css, json: loadedScreenData.data.json });
    
    //refresh the loader
    refreshPreview();

    //Update the tabs
    editor.on("languageChanged", (language, model) => {
        $("#monaco-tabs.tabs li").removeClass("is-active");
        $("#monaco-tabs.tabs li[data-lang="+language+"]").addClass("is-active");
    });

    //Save the document
    editor.on("save", async (models) => {

        let content = {
            html: models.html.getValue(),
            css: models.css.getValue(),
            js: models.js.getValue(),
            json: models.json.getValue(),
        }

        await fetch('/api/screen/24f067c8-853a-11ea-bc55-0242ac130003', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(content)
        });

        console.log("Content Saved");
        refreshPreview();
        generateSchema();
    });

    //Just refresh the preview
    editor.on("run", () => {
        refreshPreview();
        generateSchema();
    });

    //Tab Logic
    $("#monaco-tabs.tabs a").click((data) => {
        let $parent = $(data.target).parent();
        editor.setLanguage($parent.data("lang"));
    });
    
    function generateSchema() {
        let json = editor.getValue('json');
        if (json != null) {
            let obj = JSON.parse(json);
            let schema = GenerateSchema.json('Schema', obj);
            editor.setJsonSuggestions(schema);
        }
    }

    //Resize Logic
    window.addEventListener('resize', () => { console.log("resize"); resizePreview(); });
    function resizePreview() {

        const width = parseInt($monaco.css('width'), 10);
        
        let scale = width / 1920.0;
        let height = 1080.0*scale;
        $monaco.css('height', height);

        $iframe.parent().css({ 
            width: width,
            height: height,
        });
        
        $iframe.css('transform', `scale(${scale})`);
    }

    function refreshPreview() {

        $iframe.fadeOut();
        $iframe.get(0).src = '/player/24f067c8-853a-11ea-bc55-0242ac130003/';
        $iframe.on("load", () => {
            $iframe.fadeIn();
            resizePreview();
        });
    }
});