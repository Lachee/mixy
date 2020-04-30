const WebSocket = require('ws');
const NodeCache = require('node-cache');
import {Consumer} from './Consumer';

const wss = new WebSocket.Server({
    port: 6499,  //MIXY
});

let activeConsumers = new NodeCache({stdTTL: TTL_ACTIVE, checkperiod: TTL_ACTIVE_CHECK, useClones: false, deleteOnExpire: true });
activeConsumers.on('expired', (key, value) => { value.close('Exceeded maxium active time'); console.log("Consumer Expired", value.uuid); })

wss.on('connection', (ws) => {

});