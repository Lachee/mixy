import * as monaco from "monaco-editor/esm/vs/editor/editor.api";	//Imports Monaco
import Mixy from "../mixy/mixy";									//Imports my Mixy Library. Webpack externals rule overrides this.

(async function () {
	// create div to avoid needing a HtmlWebpackPlugin template
	const div = document.createElement('div');
	div.id = 'root';
	div.style = 'width:800px; height:600px; border:1px solid #ccc;';
	document.body.appendChild(div);


	// validation settings
	monaco.languages.typescript.javascriptDefaults.setDiagnosticsOptions({
		noSemanticValidation: true,
		noSyntaxValidation: false
	});

	// compiler options
	monaco.languages.typescript.javascriptDefaults.setCompilerOptions({
		target: monaco.languages.typescript.ScriptTarget.ES6,
		allowNonTsExtensions: true,
		allowJs: true,
	});

	
	//This does work, but having to redefine my library in here would be less than ideal
	const fact = `declare namespace custom { export function onMyEvent(event: customClass): void;
		export class customClass { 
			customProperty: string;
		}`;
	monaco.languages.typescript.javascriptDefaults.addExtraLib(fact, 'myCustomNamespace');
	
	//This doesn't work. No errors occur, but there is also no auto-complete for 'Mixy' or 'mixy' or '(new Mixy())'
	let response = await fetch('/dist/mixy.js');
	let body = await response.text();
	monaco.languages.typescript.javascriptDefaults.addExtraLib(body, 'mixyjs');


	let editor = monaco.editor.create(
		document.getElementById('root'),
		{
			value: 'var a = 1;',
			language: 'javascript',
			theme: 'vs-dark'
		}
	);
})();

/*
editor.onDidChangeCursorPosition((e) => {
	const code = editor.getValue();
	const offset = editor.getModel().getOffsetAt(e.position);
	const start = code.indexOf("<script>", offset);
	const end = code.indexOf("</script>", offset);

	if (start > end || (start == -1 && end != -1)) {
		console.log(offset, start, end, "JS");
		monaco.editor.setModelLanguage(editor.getModel(), "javascript");
	}  else {
		console.log(offset, start, end, "HTML");
		monaco.editor.setModelLanguage(editor.getModel(), "html");
	}
});
*/