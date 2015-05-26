var ws;

function renderMsg(message) {
    var div = document.createElement("div");
    var content = document.createTextNode(message);

    div.appendChild(content);

    document.body.appendChild(div);
}

function onopen(evt) {
    console.log('Websocket connection opened');
}

function onclose(evt) {
    console.log('Websocket connection closed');
    setTimeout(function() {
        createWsConn();
    }, 1000);
}

function onmessage(evt) {
    console.log('Message: ',evt.data);
    renderMsg(evt.data);
}

function onerror(evt) {
        console.log('Error: ', evt.data);
}

function createWsConn() {
    ws = new WebSocket('ws://cfc.jmgpena.net/ws');

    ws.onopen = onopen;
    ws.onclose = onclose;

    ws.onmessage = onmessage;

    ws.onerror = onerror;
}

createWsConn();
