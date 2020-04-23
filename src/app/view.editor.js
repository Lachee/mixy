import { MonacoEditor } from "../monaco/index";


$(document).ready(() => {
    const container = $("#monaco-editor").get(0);
    const editor = new MonacoEditor(container);
    
    editor.on("languageChanged", (language, model) => {
        console.log(language, model);
        $("#monaco-column .tabs li").removeClass("is-active");
        $("#monaco-column .tabs li[data-lang="+language+"]").addClass("is-active");
    });

    editor.on("save", (models) => {
        console.log("save models", models);
    });

    editor.on("run", () => {
        //TODO: refresh the stuff
    });

    $("#monaco-column .tabs a").click((data) => {
        let $parent = $(data.target).parent();
        editor.setLanguage($parent.data("lang"));
    });
});