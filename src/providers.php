<?php

$providers = $app['controllers_factory'];

$providers->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Provider order by id desc", array() ) );
});

$providers->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Provider where id = ?", array($id) ) );
});

$providers->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Provider', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$providers->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Provider', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $providers;