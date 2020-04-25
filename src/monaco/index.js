import './bulma-mod.css';
import * as monaco from "monaco-editor/esm/vs/editor/editor.api";	//Imports Monaco
import util from 'util';
import EventEmitter from 'events';

/** Wrapper around the monaco editor that provides a clean default enviroment
 * events:
 * 	languageChanged
 * 	save
 * 	run
 */
export class MonacoEditor extends EventEmitter{

	#editor = null;
	#languageModels = { 'js': {}, 'css': {}, 'html': {}, 'json': {}};
	#languageStates = { 'js': {}, 'css': {}, 'html': {}, 'json': {}};
	#currentLanguage = 'js';

	constructor(container) {
		super();
		const self = this;

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
			libs: [ '@tweenjs/tween.js']
		});

		// Import the types from the file
		import( './types.js');
		
		// Create the monaco this.#editor
		this.#editor = monaco.editor.create(
			container,
			{ theme: 'vs-dark', automaticLayout: true }
		);

		// Create a custom action to switch between stuff
		this.#editor.addAction({
			id: 'mixy.language.switch.html',
			label: 'Go to HTML',	
			keybindings: [
				monaco.KeyMod.chord(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_H)
			],
			precondition: null,
			keybindingContext: null,	
			contextMenuGroupId: 'navigation',	
			contextMenuOrder: 1.5,
			run: function(ed) { self.#changeLanguage('html'); }
		});
		this.#editor.addAction({
			id: 'mixy.language.switch.js',
			label: 'Go to JavaScript',	
			keybindings: [
				monaco.KeyMod.chord(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_J)
			],
			precondition: null,
			keybindingContext: null,	
			contextMenuGroupId: 'navigation',	
			contextMenuOrder: 1.5,
			run: function(ed) { self.#changeLanguage('js'); }
		});
		this.#editor.addAction({
			id: 'mixy.language.switch.css',
			label: 'Go to CSS',	
			keybindings: [
				monaco.KeyMod.chord(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_K)
			],
			precondition: null,
			keybindingContext: null,	
			contextMenuGroupId: 'navigation',	
			contextMenuOrder: 1.5,
			run: function(ed) { self.#changeLanguage('css'); }
		});		
		this.#editor.addAction({
			id: 'mixy.language.switch.json',
			label: 'Go to JSON Schema',	
			keybindings: [
				monaco.KeyMod.chord(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_L)
			],
			precondition: null,
			keybindingContext: null,	
			contextMenuGroupId: 'navigation',	
			contextMenuOrder: 1.5,
			run: function(ed) { self.#changeLanguage('json'); }
		});
		this.#editor.addAction({
			id: 'mixy.save',
			label: 'Save',	
			keybindings: [
				monaco.KeyMod.chord(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_S)
			],
			precondition: null,
			keybindingContext: null,	
			contextMenuGroupId: null,	
			contextMenuOrder: 1.5,
			run: function(ed) {  self.emit("save", self.#languageModels); }
		});
		this.#editor.addAction({
			id: 'mixy.run',
			label: 'Reload Preview',	
			keybindings: [
				monaco.KeyMod.chord(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_R)
			],
			precondition: null,
			keybindingContext: null,	
			contextMenuGroupId: null,	
			contextMenuOrder: 1.5,
			run: function(ed) { self.emit('run'); }
		});

		//setup the languages
		this.#languageModels.js = monaco.editor.createModel(`
const size = 100;
const speed = 1000;

// data tweening
createjs.Ticker.setFPS(60);
createjs.Tween.get({opacity:0,x:0}, {loop:true, onChange:render})
  .to({x:0,         y:0         }, 0, createjs.Ease.bounceOut)
  .to({x:0,         y:1080-size }, speed, createjs.Ease.bounceOut)
  .to({x:1920-size, y:1080-size }, speed, createjs.Ease.bounceOut)
  .to({x:1920-size, y:0         }, speed, createjs.Ease.bounceOut)
  .to({x:0,         y:0         }, speed, createjs.Ease.bounceOut);

function render(event){
  const avatar = document.getElementById('ball');
  var data = event.currentTarget.target;
  avatar.style.transform = \`translateX(\${data.x}px) translateY(\${data.y}px)\`;
}
`,'javascript');

		this.#languageModels.css = monaco.editor.createModel(`
#ball { 
	width: 100px; 
	height: 100px; 
	background: #00d1b2;
	position: absolute;
	border-radius: 100%;
}`, 'css' );
		this.#languageModels.html = monaco.editor.createModel('<div id="ball"></div>', 'html' );
		this.#languageModels.json = monaco.editor.createModel('{\n\n}', 'json' );

		//set the initial model to JS
		this.#editor.setModel(this.#languageModels.js);
	}

	/** Sets the schema suggestions for JSON */
	async setJsonSuggestions(schema) {
		compile = await import(/* webpackChunkName: "jstt" */ "json-schema-to-typescript");
		console.log("COmpile:", compile);

		ts = await compile(schema, 'settings');		
		console.log(schema, "=>", ts);
		monaco.languages.typescript.javascriptDefaults.addExtraLib(ts, 'js-schema.d.ts');
	}
		
	/** Sets a language */
	setLanguage(language) {
		return this.#editor.trigger("monacolib", `mixy.language.switch.${language}`);
	}

	/** Gets the current language */
	get language() { 
		return this.#currentLanguage;
	}

	/** Sets the code for a language */
	setValue(language, code) {
		console.log("load", language, code);
		this.#languageModels[language].setValue(code);
	}

	/** Sets the code for multiple languages */
	setValues(codes) {
		for(let language in codes) {
			this.setValue(language, codes[language]);
		}
	}

	/** Gets the value of a language */
	getValue(language) {
		return this.#languageModels[language].getValue();
	}

	/** Triggers a Command Pallette action */
	triggerAction(source, handler, payload) {
		return this.#editor.trigger(source, handler, payload);
	}
	
	/** Changes the editor language and invokes events */
	#changeLanguage(language) {

		//Save the current language state
		this.#languageStates[this.#currentLanguage] = this.#editor.saveViewState();
	
		//Apply the new language state
		this.#editor.setModel(this.#languageModels[language]);
		this.#editor.restoreViewState(this.#languageStates[language]);
		this.#editor.focus();
	
		//Remeber our new langauge
		this.#currentLanguage = language;
		this.emit("languageChanged", language, this.#languageModels[language]);

	}


}
