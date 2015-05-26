<?php

namespace Server;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MessagePusher implements MessageComponentInterface {
    protected $clients;
    protected $workers;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->workers = new \SplObjectStorage;
    }

    private function isWorker($conn) {
        $ip = $conn->WebSocket->request->getHeader('X-Real-IP');

        // if there is a X-Real-IP header then it's not a local worker
        // the header is added by nginx so the client has no control over it
        return (!$ip);
    }

    private function pushMessage($from, $msg) {
        echo "Message from ({$from->resourceId}): ({$msg})\n";

        if ( $this->clients->count() > 0 ) {
            // broadcast message to all clients
            $from->send('OK');
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
        } else {
            $from->send('WAITING');
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        if ( $this->isWorker($conn) ) {
            $this->workers->attach($conn);
            echo "New worker! ({$conn->resourceId})\n";
        } else {
            $this->clients->attach($conn);
            echo "New client! ({$conn->resourceId})\n";
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        if ( $this->workers->contains($from) ) {
            $this->pushMessage($from, $msg);
        } else {
            echo "Ignoring this message...\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if ( $this->clients->contains($conn) ) {
            $this->clients->detach($conn);
        } else {
            $this->workers->detach($conn);
        }
        echo "Connection ({$conn->resourceId}) has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}