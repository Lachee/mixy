
import EventEmitter from 'events';

/** Handles communication back from the server */
export class MixerClient extends EventEmitter {
    
    constructor(host = null, endpoint = '/ws/') {
        super();
        this.secure         = true;
        this.protocol       = 'wss:';
        this.port           = location.port || 80;
        this.endpoint       = endpoint;
        this.host           = host;

        this.nonce = 0;
        this.shortCode = null;
        this.user = null;
        this.channel = null;

        if (!this.host) {
            this.secure     = location.protocol == 'https:';
            this.protocol   = this.secure ? 'wss:' : 'ws:';            
            this.port       = 6499;
            
            let portStr     = this.port == 80 ? '' : ":" + this.port;
            this.host       = `${this.protocol}//${location.hostname}${portStr}${endpoint}`;
        }

        this.ws = new WebSocket(this.host);
        this.ws.addEventListener('open', (e) => { 
            console.log("Opened", e);
            this.send('HANDSHAKE', { 
                'token': location.pathname.split('/')[2] 
            });
            this.emit('open', e); 
        });

        this.ws.addEventListener('close', (e) => { 
            console.error("Closed!", e.code, e.reason);
        });

        this.ws.addEventListener('close', (e) => { this.emit('close', e); });
        this.ws.addEventListener('message', (e) => {
            var data = JSON.parse(e.data);
            console.log("message", data);
            this.emit('message', data);
        });
    }

    /** Sends a event to the server */
    send(event, payload) {
        var obj = {
            e: event,
            n: this.nonce++,
            d: payload
        };

        this.ws.send(JSON.stringify(obj));
    }
    
}