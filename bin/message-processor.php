<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use WebSocket\Client;
use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('127.0.0.1');

function publishMessage($message) {
    $client = new Client("ws://localhost:10800");
    $client->send($message);

    $result = $client->receive();

    return $result == "OK";
}

while (true) {
    global $pheanstalk;

    $job = $pheanstalk
         ->watch('cfm')
         ->ignore('default')
         ->reserve();

    print_r($job);

    echo "Reserved job: {$job->getId()}";
    $data = $job->getData();

    if ( publishMessage($data) ) {
        $pheanstalk->delete($job);
        echo "deleted job: {$job->getId()}";
    } else {
        $pheanstalk->release($job);
        echo "released job: {$job->getId()}";
        sleep(1);
    }
}


