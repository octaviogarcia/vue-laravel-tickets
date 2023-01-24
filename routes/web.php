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

Route::get('/ticket_viewer',function(){
  return view_with_variables('ticket_viewer',['server_time' => (new DateTimeImmutable())->getTimestamp()]);
});
Route::get('/ticket_list',function(){
  return view_with_variables('ticket_list',['server_time' => (new DateTimeImmutable())->getTimestamp()]);
});

