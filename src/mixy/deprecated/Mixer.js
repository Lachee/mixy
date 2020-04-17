import EventEmitter from 'eventemitter3';

export class Mixer extends EventEmitter {

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
    }
    
    connect() {
        this.log('initializing websocket...', this.host);
        this.ws = new WebSocket(this.host);
        this.ws.addEventListener('open', (e) => { this.emit('open', e); });
        this.ws.addEventListener('close', (e) => { this.emit('close', e); });
        this.ws.addEventListener('message', (e) => {
            let data = JSON.parse(e.data);            
            let event = data.e;
            let payload = data.p;
            let nonce = data.n;

            //Emit the event right back
            this.log(`[${nonce}] ${event}`, payload);
            this.emit(event, payload, nonce);

            //Special cases
            switch(event) {
                case 'MIXER_CODE_ACCEPT':
                    this.shortCode = payload;
                    this.emit('codeAccepted', payload);
                    break;

                case 'MIXER_CODE_EXPIRE':
                    this.shortCode = null;
                    this.emit('codeExpired');
                    break;

                case 'MIXER_CODE_WAIT':
                    this.shortCode = payload;
                    this.emit('codePending', payload);
                    break;

                case 'MIXER_OPEN':
                    this.emit('mixerReady');
                    break;

                case 'MIXER_IDENTIFY':
                    this.user = payload;
                    this.channel = this.user.channel;
                    this.emit('identify', this.user);
                    break;

                case 'ERROR':
                    console.error(e);
                    this.emit('error', e);
                    break;

                case 'PLAY_READY':
                    this.emit('ready');
                    break;

                default:
                    if (event.startsWith('PLAY_')) {
                        let name = this._stc(event.substring(5));
                        this.log("Play Event", name);
                        this.emit(name, payload, 'play', nonce);
                    } 
                    else if (event.startsWith('CARNIA_')) {
                        let name = this._stc(event.substring(7));
                        this.log("Carnia Event", name);
                        this.emit(name, payload, 'carnia', nonce);
                    }
                    break;

            }
        });
    }

    log(...args) {
        console.log("[mixer]", ...args);
    }

    //TODO: Fix This
    _stc(str) {
        return str.replace(
            /([-_][a-z])/g,
            (group) => group.toUpperCase()
                        .replace('-', '')
                        .replace('_', '')
        );
    }
}