<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Server\MessagePusher;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MessagePusher()
        )
    ),
    10800
);

$server->run();