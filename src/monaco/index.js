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
	#languageModels = { 'js': {}, 'css': {}, 'html': {}};
	#languageStates = { 'js': {}, 'css': {}, 'html': {}};
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
		});

		// Import the types from the file
		import( './types.js');
		
		// Create the monaco this.#editor
		this.#editor = monaco.editor.create(
			container,
			{ theme: 'vs-dark' }
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
			id: 'mixy.save.run',
			label: 'Save & Run',	
			keybindings: [
				monaco.KeyMod.chord(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KEY_H)
			],
			precondition: null,
			keybindingContext: null,	
			contextMenuGroupId: null,	
			contextMenuOrder: 1.5,
			run: function(ed) { self.emit("save", self.#languageModels); self.emit('run'); }
		});

		//setup the languages
		this.#languageModels.js = monaco.editor.createModel('var success = mixy.mixerLogin();\nalert(success);','javascript');
		this.#languageModels.css = monaco.editor.createModel( '.container {\n}', 'css' );
		this.#languageModels.html = monaco.editor.createModel(  '<div class="container">\n<!-- stuff here -->\n</div>', 'html' );

		//set the initial model to JS
		this.#editor.setModel(this.#languageModels.js);
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
