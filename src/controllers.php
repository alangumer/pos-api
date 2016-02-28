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


/* start stores */
$app->get('/stores', function () use ($app) {
  return $app->json( $app['db']->fetchAll("SELECT * FROM Store", array()) );
});

$app->get('/stores/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Store where id = ?", array($id) ) );
});

$app->put('/stores/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Store', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});
/* end stores */


/* start customersTypes */
$app->get('/customersTypes', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM CustomerType order by id desc", array() ) );
});

$app->get('/customersTypes/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM CustomerType where id = ?", array($id) ) );
});

$app->post('/customersTypes/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('CustomerType', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$app->put('/customersTypes/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'CustomerType', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});
/* end customersTypes */


/* start customers */
$app->get('/customers', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Customer order by id desc", array() ) );
});

$app->get('/customers/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Customer where id = ?", array($id) ) );
});

$app->post('/customers/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Customer', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$app->put('/customers/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Customer', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});
/* end customers */

/* start products */
$app->get('/products', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT p.*, c.name category_name FROM Product p join Category c on c.id = p.category_id order by id desc", array() ) );
});

$app->get('/products/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Product where id = ?", array($id) ) );
});

$app->post('/products/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Product', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$app->put('/products/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Product', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});
/* end products */


/* start categories */
$app->get('/categories', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Category order by id desc", array() ) );
});

$app->get('/categories/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Category where id = ?", array($id) ) );
});

$app->post('/categories/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Category', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$app->put('/categories/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Category', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});
/* end categories */


/* start banks */
$app->get('/banks', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Bank order by id desc", array() ) );
});

$app->get('/banks/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Bank where id = ?", array($id) ) );
});

$app->post('/banks/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Bank', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$app->put('/banks/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Bank', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});
/* end banks */


/* start providers */
$app->get('/providers', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Provider order by id desc", array() ) );
});

$app->get('/providers/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Provider where id = ?", array($id) ) );
});

$app->post('/providers/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Provider', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$app->put('/providers/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Provider', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});
/* end providers */



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
