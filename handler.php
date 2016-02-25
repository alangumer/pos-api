<?php
require_once __DIR__.'/vendor/autoload.php';

use Silex\Application;

$app = new Application();
// production environment - false; test environment - true
$app['debug'] = true;
 
$list = array(
 '00001'=> array(
    'name' => 'Peter Jackson',
    'description' => 'Producer | Director',
    'image' => 'MV5BMTY1MzQ3NjA2OV5BMl5BanBnXkFtZTcwNTExOTA5OA@@._V1_SY317_CR8,0,214,317_AL_.jpg',
 ),
 '00002' => array(
    'name' => 'Evangeline Lilly',
    'description' => 'Actress',
    'image' => 'MV5BMjEwOTA1MTcxOF5BMl5BanBnXkFtZTcwMDQyMjU5MQ@@._V1_SY317_CR24,0,214,317_AL_.jpg',
 ),
);
 
$app->get('/', function() use ($list) {
 
 return json_encode($list);
});
 
$app->get('/{id}', function (Silex\Application $app, $id) use ($list) {
 
 if (!isset($list[$id])) {
     $app->abort(404, "id {$id} does not exist.");
 }
 return json_encode($list[$id]);
});
 
$app->run();
?>