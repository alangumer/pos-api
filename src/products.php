<?php

$products = $app['controllers_factory'];

$products->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT p.*, c.name category_name FROM Product p join Category c on c.id = p.category_id order by id desc", array() ) );
});

$products->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Product where id = ?", array($id) ) );
});

$products->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->insert('Product', $data);
  return $app->json( formatResponse( $app['db']->lastInsertId() ) );
});

$products->put('/edit', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );

  $app['db']->update( 'Product', $data, array( 'id' => $data['id'] ) );
  return $app->json( formatResponse( $data['id'] ) );
});

return $products;