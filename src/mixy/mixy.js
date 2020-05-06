import './mixy.scss';        //Page styling
import './oAuthPopup.js';   //Library that adds "document.createModal"

import {MixerClient} from './MixerClient.js';
import { ShortCodeExpireError, ShortCodeAccessDeniedError, OAuthClient }  from '@mixer/shortcode-oauth';
import EventEmitter from 'events';

/** Handles co */
export class Mixy extends EventEmitter {

    /** OAuthClient used by the shortcode. Using experimental privates from babel */
    #mixerOAuthClient;
    #shortCodeModal;
    opts;
    #mixer;

    constructor(opts) {
        super();
        this.opts = opts;
        console.error("constructor", this.options, this);
        //this.#mixerOAuthClient = new OAuthClient(options);
    }

    /** Attempts to perform a login */
    async mixerLogin() {
        

        let modal = this.#getShortCodeModal();
        console.log("mixerLogin", this.options, this);
        if (!this.#mixerOAuthClient)
            this.#mixerOAuthClient = new OAuthClient(this.options.oAuth);

        const self = this;
        //let modal = this.#getShortCodeModal();
        modal.show();

        const attempt = async function() {
            try 
            {
                //Wait for the code
                let shortCode = await self.#mixerOAuthClient.getCode();
                modal.code(shortCode.code);
                modal.openWindow();

                //Wait for the tokens
                let tokens = await shortCode.waitForAccept();
                return tokens;
            } 
            catch(error) 
            {
                if (error instanceof ShortCodeExpireError)
                    return await attempt();
                
                if (error instanceof ShortCodeAccessDeniedError)
                    return false;

                throw error;
            }
        };

        //Attempt to fetch the tokens
        let tokens = await attempt();

        //Close the modal windows
        modal.hide(); 
        modal.closeWindow();

        if (tokens) {

            //Store the tokens
            let response = await fetch('/auth', {
                method: 'POST',
                credentials: 'include',
                headers: { 'content-type': 'application/json' },
                body: JSON.stringify(tokens),
            });

            if (response.ok) return true;
            return response.statusText;
        }

        return false;
    }
    #getShortCodeModal() {
        if (this.#shortCodeModal != null) return this.#shortCodeModal;

        const self = this;
        this.#shortCodeModal = document.createModal(`
            <section class="hero is-primary">
            <div class="hero-body">
                <div class="container">
                <h1 class="title">Mixer Short Code</h1>
                <h1 class="title"> <input onClick="this.setSelectionRange(0, this.value.length)" name="shortcode" type="text" class="shortcode  has-background-transparent" readonly value="....."></h1>
                <br>
                <h2 class="subtitle">
                    <a href="#" class="button oauth is-centered is-secondary is-large oauth"><span class="icon is-medium"><i class="fab fa-mixer"></i></span><span>mixer.com / go</span></a>
                </h2>
                </div>
            </div>
            
            <div class='row'></div>
            </div>
            `, { boxClass: 'has-text-centered is-centered', showClose: false});

        //Hook the code function up
        this.#shortCodeModal.code = function(val = undefined) {
            if (val === undefined) return  $(self.#shortCodeModal).find('input[name=shortcode]').val();
            return $(self.#shortCodeModal).find('input[name=shortcode]').val(val);
        };

        //Create a functin to open window
        this.#shortCodeModal.openWindow = function() {
            self.#shortCodeModal.oauthWindow = window.openOAuthWindow('https://mixer.com/go?code=' + self.#shortCodeModal.code(), { 
                windowOptions: {
                    center: true,
                    width: 560,
                    height: 600 
                }
            });
        }

        this.#shortCodeModal.closeWindow = function() {
            if (self.#shortCodeModal.oauthWindow) 
            self.#shortCodeModal.oauthWindow.close();
        }

        //Hook into the button
        $(this.#shortCodeModal).find('.oauth').click((e) => {
            e.preventDefault();
            self.#shortCodeModal.openWindow();
        });

        return this.#shortCodeModal;
       
    }

    get options() { return this.opts; }

    connect() {
        this.#mixer = new MixerClient();
        
    }
}
