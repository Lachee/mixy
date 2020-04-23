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

        // .. Does Token Stuff ..

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

        // .. Creates popup modals ..

        return this.#shortCodeModal;
    }
}