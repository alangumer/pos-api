<?php

$paymentsTypes = $app['controllers_factory'];

$paymentsTypes->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM PaymentType order by id desc", array() ) );
});

$paymentsTypes->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM PaymentType where id = ?", array($id) ) );
});

$paymentsTypes->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('PaymentType', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$paymentsTypes->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'PaymentType', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $paymentsTypes;