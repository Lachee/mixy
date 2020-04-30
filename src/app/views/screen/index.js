import { JSONEditor } from '@json-editor/json-editor';
import  GenerateSchema   from 'generate-schema';

export function createSchemaEditor(options) {

    if (options.default) {
        options.schema = GenerateSchema.json('Schema', options.default);
        console.log(options.schema);
        delete options.default;
    }

    const value = options.value;
    delete options.value;
    
    const editorElement = document.getElementById('json-editor');
    const editor = new JSONEditor(editorElement, options);
    
    editor.on('change', async () => {
        editorElement.querySelectorAll('.btn').forEach(function(item)  {
            item.classList.add('button');
            item.classList.add('is-small');
            item.classList.add('is-info');
            item.classList.add('is-outlined');
        });
        editorElement.querySelectorAll('input').forEach(function(item)  {
            item.classList.add('input');
            item.classList.add('is-small');
        });
        editorElement.querySelectorAll('.delete').forEach(function(item)  {
            item.classList.remove('delete');
        });
        editorElement.querySelectorAll('.selectize-input').forEach(function(item)  {
            item.classList.add('select');
            item.classList.add('is-small');
        });
        editorElement.querySelectorAll('[data-schematype=boolean]').forEach(function(item)  {

            let label = item.querySelector('label').textContent;
            let name = item.querySelector('select').name;
            let value = item.querySelector('select').value;
            let checked = value == 1 ? 'checked' : '';
            
            let html = `<br><input id="${name}" type="checkbox" name="${name}" class="switch" ${checked}><label for="${name}">${label}</label>`;
            item.innerHTML = html + item.innerHTML;

            item.querySelector('label.je-label').remove();
            item.querySelector('select').remove();

            //item.outerHTML = `<div class="field"><input id="switchColorDefault" type="checkbox" name="switchColorDefault" class="switch" checked="checked"><label for="switchColorDefault">Switch default</label></div>`;
        });

        setInterval(() => {
            editor.setValue(value);
        }, 1000);

    });

}

