const { ShortCodeExpireError }  = require('@mixer/shortcode-oauth');
const interactive = require('@mixer/interactive-node');
const fetch = require('node-fetch');
const EventEmitter = require('events');

interactive.setWebSocket(require('ws'));

module.exports = class Consumer extends EventEmitter {
    constructor(app, uuid, ws) {
        super();
        this.app = app;
        this.uuid = uuid;
        this.ws = ws;
        
        this.authetnicationToken = null;
        this.authentication = null;
        this.accessToken = null;
        this.nonce = 0;
    }

    init() {
        const self = this;

        this.ws.on('message', message => {
            console.log(self.uuid, message);
            let blob = JSON.parse(message);
            switch(blob.e) {

                //Auth system message
                case "HANDSHAKE":

                    //Authenticate the token
                    self.app.validateAuthenticationAsync(blob.d.token).then((authentication) => {

                        //Store the authenticated shit
                        self.authetnicationToken = blob.d.token;
                        self.authentication = authentication;
                        self.emit("authenticated");

                        //If we are valid, then login
                        // We will catch if we failed so we can close the connection
                        if (self.validate()) {                            
                            self.login().then((authed) => {
                                if (!authed) {
                                    self.emit("deauthenticated");
                                    self.close("deauthenticated");
                                }

                                //Now lets just say hello
                                self.send("HELLO", { a: authentication, at: self.accessToken });
                            });
                            
                        }
                    });
                    break;

                //Anything else
                default:
                    console.warn("Unkown Event", blob.e);
                    self.validate();
                    break;
            }
        });

        this.ws.on('close', reason => {
            self.close(reason);
        });
    }

    /** Finds the access token */
    async login() {
        //Get the access token from oauth
        this.accessToken = await this.app.oauthStorage.getAccessToken(this.authentication.sub);
        if (!this.accessToken) {
            //We have no access token, so we need to tell the site to refresh the token
            let success = await this.app.refreshAuthenticationAsync(this.authetnicationToken);
            if (success) this.accessToken = await this.app.oauthStorage.getAccessToken(this.authentication.sub);
        }

        return this.accessToken !== false && this.accessToken !== null;
    }

    /** Enforces validation check */
    validate() { 
        if (this.authentication === null) {
            console.error("Connection not authenticated!");
            this.close("not authenticated");
            return false;
        }

        if (this.accessToken == null) {
            console.error("Connection doesn't have an access token");
            this.close("not logged in");
            return false;
        }

        return true;
    }

    /** Closes down the consumer */
    close(reason) {
        if (this.ws) {
            this.ws.send(reason);
            this.ws.close();
            this.ws = null;
        }

        if (this.controller) {
            this.controller.onClose(reason);
        }

        //Execute the event
        this.emit("close");
    }

    /** Sends an event to the client */
    send(event, payload) {
        var obj = {
            e: event,
            n: this.nonce++,
            d: payload
        };
        this.ws.send(JSON.stringify(obj));
        this.emit("sent", obj);
        console.log("SENT", obj);
    }

    
    /** Subscribes to Constellation */
    subscribe() {
        console.log('consumer subscribed', this.uuid);
        this.mixerConfig.carnia.subscribe(`channel:${this.user.channel.id}:update`, data => { 
            //Tell them we updated our channel
            this.send('CARNIA_CHANNEL_UPDATE', data);

            const previousCostreamId = this.user.channel.costreamId;

            //Update our internal channel and resend the idenfity
            this.user.channel = Object.assign(this.user.channel, data);
            console.log("MIXER_IDENTIFY", this.user);
            this.send('MIXER_IDENTIFY', this.user);

            //Have we changed? If so, we need to unsub from previous
            if (previousCostreamId != null && previousCostreamId != this.user.channel.costreamId) {
                console.log('consumer left costream', this.uuid, previousCostreamId);
                this.mixerConfig.carnia.unsubscribe(`costream:${previousCostreamId}:update`, this._carniaCostreamUpdate);
                this.send('CARNIA_COSTREAM_LEAVE', { id: previousCostreamId });
            }

            //We have a costream, so we need to join one
            if (this.user.channel.costreamId != null) {
                console.log('consumer joined costream', this.uuid, this.user.channel.costreamId);
                this.mixerConfig.carnia.subscribe(`costream:${this.user.channel.costreamId}:update`, this._carniaCostreamUpdate);
                this.send('CARNIA_COSTREAM_JOIN');
            }
        });
        
        this.mixerConfig.carnia.subscribe(`channel:${this.user.channel.id}:followed`,           data => this.send('CARNIA_CHANNEL_FOLLOWED', data));
        this.mixerConfig.carnia.subscribe(`channel:${this.user.channel.id}:hosted`,             data => this.send('CARNIA_CHANNEL_HOSTED', data));
        this.mixerConfig.carnia.subscribe(`channel:${this.user.channel.id}:subscribed`,         data => this.send('CARNIA_CHANNEL_SUBSCRIBED', data));
        this.mixerConfig.carnia.subscribe(`channel:${this.user.channel.id}:skill`,              data => this.send('CARNIA_CHANNEL_SKILL', data));
        this.mixerConfig.carnia.subscribe(`channel:${this.user.channel.id}:patronageUpdate`,    data => this.send('CARNIA_CHANNEL_PATRONAGE_UPDATE', data));
        this.mixerConfig.carnia.subscribe(`channel:${this.user.channel.id}:subscriptionGifted`, data => this.send('CARNIA_CHANNEL_SUBSCRIPTION_GIFTED', data));
        this.emit("subscribed");
        //this.mixerConfig.carnia.subscribe(`costream:${this.user.channel.id}:update`, data => this.send('CARNIA_COSTREAM_UPDATE', data));
    }

    /** Costream Update. Seperate function because Co-Streams are cross user, so I cannot unsub all. */
    _carniaCostreamUpdate(data) { this.send('CARNIA_COSTREAM_UPDATE', data); }

    /** Unsubscribes from Constellation */
    unsubscribe() {
        console.log('consumer unsubscribed', this.uuid);
        this.mixerConfig.carnia.unsubscribe(`channel:${this.user.channel.id}:update`);
        this.mixerConfig.carnia.unsubscribe(`channel:${this.user.channel.id}:followed`);
        this.mixerConfig.carnia.unsubscribe(`channel:${this.user.channel.id}:hosted`);
        this.mixerConfig.carnia.unsubscribe(`channel:${this.user.channel.id}:subscribed`);
        this.mixerConfig.carnia.unsubscribe(`channel:${this.user.channel.id}:skill`);
        this.mixerConfig.carnia.unsubscribe(`channel:${this.user.channel.id}:patronageUpdate`);
        this.mixerConfig.carnia.unsubscribe(`channel:${this.user.channel.id}:subscriptionGifted`);

        if (this.user.channel.costreamId != null) 
            this.mixerConfig.carnia.unsubscribe(`costream:${this.user.channel.costreamId}:update`, this._carniaCostreamUpdate);

        this.emit("unsubscribed");
    }

    /** fetches a mixer endpoint */
    async mixerResource(verb, endpoint, payload = null) {
        let response = await fetch(`${this.mixerConfig.MIXER_API}${endpoint}`, {
            method: verb,
            body: payload ? JSON.stringify(payload) : null,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.tokens.accessToken}`
            }
        });

        return await response.json();
    }
}