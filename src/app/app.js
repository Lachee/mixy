import './page.scss';
import 'mixy/mixy';

//Get the route and remove the first element
let route = window.location.pathname.split('/'); route.shift();

//Join the route back with dots
let filename = route[0].trim();
if (filename == "") filename = "app";

//Load the current JS for the base route
console.log('loading view file', "./views/"+filename+"/index.js");
export let view;
export const viewPromise = new Promise((resolve, reject) => {
    import(/* webpackChunkName: "view-" */ "./views/"+filename+"/index.js").then(v => {
        view = v;
        resolve(v);
    }).catch(e => reject(e));
});

//Apply "always" javascript
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