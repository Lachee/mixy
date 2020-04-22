import './mixy.css';        //Page styling
import './oAuthPopup.js';   //Library that adds "document.createModal"

import { ShortCodeExpireError, ShortCodeAccessDeniedError, OAuthClient }  from '@mixer/shortcode-oauth';

export class Mixy {

    /** OAuthClient used by the shortcode. Using experimental privates from babel */
    #mixerOAuthClient;
    #shortCodeModal;

    /** Sets teh defaults configuration from the outside */
    configureOAuth(options) {
        this.#mixerOAuthClient = new OAuthClient(options);
    }

    /** Attempts to perform a login */
    async mixerLogin() {

        let modal = this.#getShortCodeModal();
        modal.show();

        const attempt = async function() {
            try 
            {
                //Wait for the code
                let shortCode = await this.#mixerOAuthClient.getCode();
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

        //@ts-ignore
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
            if (val === undefined) return  $(this.#shortCodeModal).find('input[name=shortcode]').val();
            return $(this.#shortCodeModal).find('input[name=shortcode]').val(val);
        };

        //Create a functin to open window
        this.#shortCodeModal.openWindow = function() {

            //@ts-ignore
            this.#shortCodeModal.oauthWindow = window.openOAuthWindow('https://mixer.com/go?code=' + this.#shortCodeModal.code(), { 
                windowOptions: {
                    center: true,
                    width: 560,
                    height: 600 
                }
            });
        }

        this.#shortCodeModal.closeWindow = function() {
            if (this.#shortCodeModal.oauthWindow) 
            this.#shortCodeModal.oauthWindow.close();
        }

        //Hook into the button
        $(this.#shortCodeModal).find('.oauth').click((e) => {
            e.preventDefault();
            this.#shortCodeModal.openWindow();
        });

        return this.#shortCodeModal;
    }
}