<?php

$categories = $app['controllers_factory'];

$categories->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Category order by id desc", array() ) );
});

$categories->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Category where id = ?", array($id) ) );
});

$categories->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Category', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$categories->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Category', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $categories;