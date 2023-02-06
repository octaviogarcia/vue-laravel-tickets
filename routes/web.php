<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


function view_with_variables(string $viewname,array $vars = []){
  $vars['_token'] = csrf_token();
  $vars_js = str_replace("\\","\\\\",json_encode($vars));
  $vars_js = "<script>const blade_vars = JSON.parse('"
    .str_replace("'","\'",$vars_js)
  ."');</script>";
	return view($viewname,[
		'blade_vars' => $vars,
    'blade_vars_JS' => $vars_js,
	]);
}

Route::get('/', function () {
	return view_with_variables('app');
});

Route::get('/2', function () {
	$object = [
		'name' => 'My Complex Object',
		'array' => [
			1,2,3,4
		],
		'object' => [
			'int' => 1,
			'string' => 'asd',
			'empty_array' => [],
			'null' => null,
			'boolean' => false,
			'float' => 1.23,
		],
	];
  return view_with_variables('app2',[
    'object' => $object,
    'another_object' => ['name' => 'another object!'],
    'int_value' => 1,
    'leak_test' => '\'',
    'leak_test2' => "'",
    'leak_test3' => "\\'",
    'leak_test4' => "\\\'",
  ]);
});

function states(){ return ['ABIERTO','SOLUCIONADO','CERRADO']; };

Route::get('/ticket_viewer/{number?}',function(){
  return view_with_variables('ticket_viewer',[
    'server_time' => (new DateTimeImmutable())->getTimestamp(),
    'states' => states()
  ]);
});

Route::get('/get_ticket/{number?}',function($number = null){
  if(is_null($number)){
    return [[
      'id' => null,
      'number' => '-NUEVO-',
      'title' => '',
      'author' => '-USUARIO-',
      'state' => 'ABIERTO',
      'tags' => [],
      'text' => '',
      'files' => '',
      'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
      'modified_at' => '',
    ]];
  }
  $rand_tickets = array_fill(0,random_int(50,100),null);
  return array_map(function($v,$idx){
    return [
      'id' => random_int(0,1000000),
      'number' => random_int(0,10000),
      'title' => 'Titulo'.$idx,
      'author' => 'Autor'.random_int(0,23),
      'state' => states()[array_rand(states())],
      'tags' => array_map(function(){
        return 'tag'.random_int(0,4);
      },array_fill(0,random_int(0,4),null)),
      'text' => 'Textooooooooo'.random_int(0,100),
      'files' => array_map(function(){
        return [
          'name' => 'file'.random_int(0,4).'.jpg',
          'url' => 'files/route'.random_int(0,4).'.jpg',
        ];
      },array_fill(0,random_int(0,4),null)),
      'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
      'modified_at' => (new DateTime())->format('Y-m-d H:i:s'),
    ];
  },$rand_tickets,array_keys($rand_tickets));
});

Route::get('/ticket_list',function(){
  return view_with_variables('ticket_list',[
    'server_time' => (new DateTimeImmutable())->getTimestamp(),
    'states' => states(),
  ]);
});
Route::post('/search_tickets',function(){
  $rand_tickets = array_fill(0,random_int(50,100),null);
  return array_map(function($v,$idx){
    return [
      'number' => random_int(0,10000),
      'title' => 'Titulo'.$idx,
      'author' => 'Autor'.random_int(0,23),
      'state' => states()[array_rand(states())],
      'tags' => array_map(function(){
        return 'tag'.random_int(0,4);
      },array_fill(0,random_int(0,4),null)),
      'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
      'modified_at' => (new DateTime())->format('Y-m-d H:i:s'),
    ];
  },$rand_tickets,array_keys($rand_tickets));
});

Route::post('/save_ticket',function(){
  return request();
});
