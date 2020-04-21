import './page.scss';
import '../mixy/mixy';
import e from 'express';

export function test() { 
    console.log("yup");
}


$(document).ready(() => {
    $('#login-button').click(async () => {
        $('#login-button').addClass('is-loading');

        //Attempt to login
        let response = await mixy.mixerLogin();
        if (response !== true) {
            //If its an error, lets report it
            if (response !== false) alert(response);
        } else {
            //Success, so lets reload
            location.reload();
        }
    });
});