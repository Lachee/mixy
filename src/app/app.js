import './page.scss';
import '../mixy/mixy';

export function test() { 
    console.log("yup");
}


$(document).ready(() => {
    $('#login-button').click(async () => {
        $('#login-button').addClass('is-loading');
        await mixy.mixerLogin();
        location.reload();
    });
});