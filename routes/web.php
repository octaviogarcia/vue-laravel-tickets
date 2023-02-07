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

use App\Models\Ticket;

Route::get('/get_ticket/{number?}',function($number = null){
  return Ticket::where('parent',$number)
  ->orderBy('order','asc')->get();
});

Route::get('/ticket_list',function(){
  return view_with_variables('ticket_list',[
    'server_time' => (new DateTimeImmutable())->getTimestamp(),
    'states' => states(),
  ]);
});
Route::post('/search_tickets',function(){
  return Ticket::whereColumn('parent','=','number')->get();
});

Route::post('/save_ticket',function(){
  return DB::transaction(function(){
    $t = null;
    if(request()->id && Ticket::find(request()->id)){
      $t = Ticket::find(request()->id);
    }
    else{
      $t = new Ticket;
      while(true){
        $rint = random_int(0,2147483647);
        if(Ticket::where('number',$rint)->count() == 0){
          break;
        }
      }
      
      $t->number = $rint;
      
      if(request()->parent !== null){
        $t->parent = request()->parent;
        $t->order = max(
          Ticket::where('parent','=',$t->parent)
          ->select('order')
          ->get()->pluck('order')->toArray()
        ) + 1;
      }
      else{
        $t->parent = $rint;
        $t->order  = 0;
      }
    }
    $t->text = request()->text;
    $t->title  = request()->title;
    $t->author = request()->author;
    $t->status  = request()->status;
    $t->tags   = request()->tags;
    $t->files  = request()->files;
    $t->save();
    return $t;
  });
});
