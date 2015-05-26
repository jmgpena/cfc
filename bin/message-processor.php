<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use WebSocket\Client;
use Pheanstalk\Pheanstalk;

// Wait time in seconds when there are no clients connected
define('WAIT_TIME',1);

$pheanstalk = new Pheanstalk('127.0.0.1');

$wsClient = new Client("ws://localhost:10800");

// Publish the job/message to the websocket server
function publishMessage($client, $message) {
    $client->send($message);

    $result = $client->receive();

    $client->close();
    return $result == "OK";
}

// will read messages from beanstalkd queue and block
// when no messages are available
while (true) {
    global $pheanstalk;

    $job = $pheanstalk
         ->watch('cfm')
         ->ignore('default')
         ->reserve();

    print_r($job);

    echo "Reserved job: {$job->getId()}";
    $data = $job->getData();

    // if the message is succefull published to clients
    // it will be deleted, else we wait before trying again
    if ( publishMessage($data) ) {
        $pheanstalk->delete($job);
        echo "deleted job: {$job->getId()}";
    } else {
        $pheanstalk->release($job);
        echo "released job: {$job->getId()}";
        sleep(WAIT_TIME);
    }
}


