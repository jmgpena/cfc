<?php
// web/index.php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

$app = new Silex\Application();

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->post('/api/data', function(Request $request) use ($app) {
        $post = array(
            'id' => $request->request->get('id') + 1
        );
        return $app->json($post, 201);
    });

$app->get('/hello/{name}', function ($name) use ($app) {
        return 'Hello '.$app->escape($name);
    });

$app->run();