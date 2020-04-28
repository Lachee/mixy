
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

        this.shortCode = null;
        this.user = null;
        this.channel = null;

        if (!this.host) {
            this.secure     = location.protocol == 'https:';
            this.protocol   = this.secure ? 'wss:' : 'ws:';            
            this.port       = location.port || 80;
            
            let portStr     = this.port == 80 ? '' : ":" + this.port;
            this.host       = `${this.protocol}//${location.hostname}${portStr}${endpoint}`;
        }

        this.websocket = new WebSocket(this.host);
        this.ws.addEventListener('open', (e) => { this.emit('open', e); });
        this.ws.addEventListener('close', (e) => { this.emit('close', e); });
        this.ws.addEventListener('message', (e) => {
            this.emit('message', e);
        });
    }
    
}