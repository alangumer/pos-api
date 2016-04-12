<?php

$stores = $app['controllers_factory'];

$stores->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll("SELECT * FROM Store", array()) );
});

$stores->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Store where id = ?", array($id) ) );
});

$stores->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Store', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $stores;