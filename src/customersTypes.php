<?php

$customersTypes = $app['controllers_factory'];

$customersTypes->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM CustomerType order by id desc", array() ) );
});

$customersTypes->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM CustomerType where id = ?", array($id) ) );
});

$customersTypes->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('CustomerType', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$customersTypes->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'CustomerType', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $customersTypes;