<?php
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;


// refactor this part later
use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('127.0.0.1');

function processMessage($message) {
    global $pheanstalk;

    $pheanstalk
        ->useTube('test')
        ->put(json_encode($message)."\n");

    return true;
}

$app = new Silex\Application();

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->post('/api/message', function(Request $request) use ($app) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        if ( is_array($data) && processMessage($data) ) {
            $response = array(
                'status' => 'OK',
                'userId' => $data['userId']
            );
            return $app->json($response, 201);
        } else {
            return $app->json(array('status'=>'FAILED'), 400);
        }
    });

$app->get('/', function () use ($app) {
        global $pheanstalk;

        $job = $pheanstalk
             ->watch('test')
             ->ignore('default')
             ->reserve();
        $data = $job->getData();
        $pheanstalk->delete($job);

        return $data;
    });

$app->run();