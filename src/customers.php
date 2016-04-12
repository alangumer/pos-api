<?php

$customers = $app['controllers_factory'];

$customers->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Customer order by id desc", array() ) );
});

$customers->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Customer where id = ?", array($id) ) );
});

$customers->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Customer', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$customers->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Customer', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $customers;