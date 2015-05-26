var ws = new WebSocket('ws://ftc.dev/ws');

function renderMsg(message) {
    var div = document.createElement("div");
    var content = document.createTextNode(message);

    div.appendChild(content);

    document.body.appendChild(div);
}

ws.onopen = function(evt) {
    console.log('Websocket connection opened');
};

ws.onclose = function(evt) {
    console.log('Websocket connection closed');
};

ws.onmessage = function(evt) {
    console.log('Message: ',evt.data);
    renderMsg(evt.data);
};

ws.onerror = function(evt) {
    console.log('Error: ', evt.data);
};

