<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

function formatResponse( $id ) {
  return array( 'id' => $id, 'status' => 'OK' );
}

//handling CORS preflight request
$app->before(function (Request $request) {
  if ($request->getMethod() === "OPTIONS") {
    $response = new Response();
    // $response->headers->set("Access-Control-Allow-Origin", "*");
    $response->headers->set("Access-Control-Allow-Methods", "GET,POST,PUT,DELETE,OPTIONS");
    $response->headers->set("Access-Control-Allow-Headers", "Content-Type");
    $response->setStatusCode(200);
    return $response->send();
  }
}, Silex\Application::EARLY_EVENT);


//handling CORS respons with right headers
$app->after(function (Request $request, Response $response) {
  $response->headers->set("Access-Control-Allow-Origin", "*");
  $response->headers->set("Access-Control-Allow-Methods", "GET,POST,PUT,DELETE,OPTIONS");
  $response->headers->set("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Authorization");
});

//accepting JSON
/*$app->before(function (Request $request) {
  if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
    $data = json_decode($request->getContent(), true);
    $request->request->replace(is_array($data) ? $data : array());
  }
});*/

$app->get('/', function () use ($app) {
  return 'hi';
});

// customers
$app->mount('/customers', include 'customers.php');
// stores
$app->mount('/stores', include 'stores.php');
// customersTypes
$app->mount('/customersTypes', include 'customersTypes.php');
// products
$app->mount('/products', include 'products.php');
// categories
$app->mount('/categories', include 'categories.php');
// banks
$app->mount('/banks', include 'banks.php');
// providers
$app->mount('/providers', include 'providers.php');
// paymentsTypes
$app->mount('/paymentsTypes', include 'paymentsTypes.php');
// invoices
$app->mount('/invoices', include 'invoices.php');


$app->error(function (\Exception $e, $code) use ($app) {

    if ($app['debug'] == false) {
        return;
    }
    
    return 'error exception cont' . $e;

    // 404.html, or 40x.html, or 4xx.html, or error.html
    /*$templates = array(
        'errors/'.$code.'.html',
        'errors/'.substr($code, 0, 2).'x.html',
        'errors/'.substr($code, 0, 1).'xx.html',
        'errors/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);*/
});
