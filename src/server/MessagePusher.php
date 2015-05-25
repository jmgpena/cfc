<?php

namespace Server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MessagePusher implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        print_r($from->WebSocket->request->getHeader('X-Real-IP'));
        //print_r($from);
        if ( $from->remoteAddress == '127.0.0.1') {
            echo "Message from ({$from->resourceId}): ({$msg})\n";
        } else {
            echo "Ignoring this message...\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection ({$conn->resourceId}) has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}