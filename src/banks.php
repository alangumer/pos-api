<?php

$banks = $app['controllers_factory'];

$banks->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Bank order by id desc", array() ) );
});

$banks->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Bank where id = ?", array($id) ) );
});

$banks->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Bank', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$banks->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Bank', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $banks;