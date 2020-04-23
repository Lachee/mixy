declare const mixy: Mixy;
declare class Mixy {
    someField: string

    //configureOAuth(options : object) : void;
    
    /** Shows the mixer login form. */
    mixerLogin() : Promise<boolean>
}
