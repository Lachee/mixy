import './mixy.css';
import './oAuthPopup.js';

import { ShortCodeExpireError, ShortCodeAccessDeniedError, OAuthClient }  from '@mixer/shortcode-oauth';

/** OAuthClient used by the shortcode */
let mixerOAuthClient = null;
let shortCodeModal = null;

/** Sets teh defaults configuration from the outside */
export function configureOAuth(options) {
    mixerOAuthClient = new OAuthClient(options);
}

/** Attempts to perform a login */
export async function mixerLogin() {

    let modal = getShortCodeModal();
    modal.show();

    const attempt = async function() {
        try 
        {
            //Wait for the code
            let shortCode = await mixerOAuthClient.getCode();
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
        await fetch('/auth', {
            method: 'POST',
            credentials: 'include',
            headers: { 'content-type': 'application/json' },
            body: JSON.stringify(tokens),
        });

        //Return 
        return true;
}
    return false;
}

function getShortCodeModal() {
    if (shortCodeModal != null) return shortCodeModal;
    shortCodeModal = document.createModal(`
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
    shortCodeModal.code = function(val = undefined) {
        if (val === undefined) return  $(shortCodeModal).find('input[name=shortcode]').val();
        return $(shortCodeModal).find('input[name=shortcode]').val(val);
    };

    //Create a functin to open window
    shortCodeModal.openWindow = function() {
        shortCodeModal.oauthWindow = window.openOAuthWindow('https://mixer.com/go?code=' + shortCodeModal.code(), { 
            windowOptions: {
                center: true,
                width: 560,
                height: 600 
            }
        });
    }

    shortCodeModal.closeWindow = function() {
        if (shortCodeModal.oauthWindow) 
            shortCodeModal.oauthWindow.close();
    }

    //Hook into the button
    $(shortCodeModal).find('.oauth').click((e) => {
        e.preventDefault();
        shortCodeModal.openWindow();
    });

    return shortCodeModal;
}