<?php

use Illuminate\Support\Facades\Route;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
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

function states(){ 
  return ['ABIERTO','SOLUCIONADO','CERRADO']; 
};

function file_path(int $number,int $id,int $fnumber){
  return 'tickets/'.$number.'/'.$id.'/'.$fnumber;
}

Route::get('/login',function(){
  return view_with_variables('login');
})->name('login');

Route::post('/login',function(){
  $credentials = request()->validate([
    'email' => ['required', 'email'],
    'password' => ['required'],
  ]);
  
  if(Auth::attempt($credentials)){
    session()->regenerate();
    return ['redirect' => url('ticket_list')];
  }
  
  return response(['message' => 'Incorrect credentials'],401);
});

Route::get('user_create',function(){
  return view_with_variables('login',['creating_user' => true]);
});

Route::post('user_create',function(){
  $credentials = request()->validate([
    'email' => ['required', 'email'],
    'name' => ['required'],
    'password' => ['required'],
  ]);
  if(User::where('email',$credentials['email'])->count() > 0){
    return response(['message' => 'User exists'],401);
  }
  $u = new User;
  $u->name              = $credentials['name'];
  $u->email             = $credentials['email'];
  $u->password          = Hash::make($credentials['password']);
  $u->email_verified_at = date("c");
  $u->save();
  return ['message' => 'User created'];
});

Route::get('logout',function(){
  Auth::logout(Auth::user());
  return redirect('login');
});

Route::get('/',function(){
  return redirect('ticket_list');
});

Route::group(['middleware' => 'auth'],function(){
  Route::get('/ticket_viewer/{number?}',function(){
    return view_with_variables('ticket_viewer',[
      'server_time' => (new DateTimeImmutable())->getTimestamp(),
      'states' => states()
    ]);
  });
  
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
    $R = request();
    Validator::make($R->all(),[
      'number' => 'nullable|integer',
      'title' => 'nullable|string',
      'author' => 'nullable|string',
      'status' => 'nullable|string',
      'text' => 'nullable|string',
      'tags' => 'nullable|array',
      'tags.*' => 'nullable|string',
      'created_at' => 'nullable|array',
      'created_at.*' => 'nullable|date',
      'updated_at' => 'nullable|array',
      'updated_at.*' => 'nullable|date',
    ])->validate();
    
    $rules = [];
    
    if(!empty($R->number)){
      $rules[] = ['number','=',$R->number];
    }
    if(!empty($R->title)){
      $rules[] = ['title','LIKE','%'.$R->title.'%'];
    }
    if(!empty($R->author)){
      $rules[] = ['author','LIKE','%'.$R->author.'%'];
    }
    if(!empty($R->status)){
      $rules[] = ['status','=',$R->status];
    }
    if(!empty($R->text)){
      $rules[] = ['text','LIKE','%'.$R->text.'%'];
    }
    
    $rtrn = Ticket::whereColumn('parent','=','number')
    ->where($rules);
    
    $tags = array_filter($R->tags ?? [],function($t){ return $t !== null && strlen($t) > 0; });
    if(!empty($tags)){
      $rtrn = $rtrn->whereJsonContains('tags',$tags);
    }
    
    if(!empty($R->created_at)){
      if(!empty($R->created_at[0])){
        $rtrn = $rtrn->whereDate('created_at','>=',$R->created_at[0]);
      }
      if(!empty($R->created_at[1])){
        $rtrn = $rtrn->whereDate('created_at','<=',$R->created_at[1]);
      }
    }
    
    if(!empty($R->updated_at)){
      if(!empty($R->updated_at[0])){
        $rtrn = $rtrn->whereDate('updated_at','>=',$R->updated_at[0]);
      }
      if(!empty($R->updated_at[1])){
        $rtrn = $rtrn->whereDate('updated_at','<=',$R->updated_at[1]);
      }
    }
    
    if($R->order && $R->order['column']){
      $rtrn->orderBy($R->order['column'],$R->order['asc']? 'asc' : 'desc');
    }
    
    return $rtrn->get();
  });

  Route::post('/save_ticket',function(){
    $R = request();
    
    Validator::make($R->all(),[
      'number'  => 'nullable|integer',
      'title'   => 'nullable|string',
      'author'  => 'nullable|string',
      'status'  => 'nullable|string',
      'text'    => 'nullable|string',
      'tags'    => 'nullable|array',
      'tags.*'  => 'nullable|string',
      'old_files'   => 'nullable|array',
      'old_files.*' => 'nullable|string',
    ])->validate();
    
    return DB::transaction(function() use ($R){
      $t = null;
      if(request()->id && Ticket::find(request()->id)){
        $t = Ticket::find($R->id);
      }
      else{
        $t = new Ticket;
        while(true){
          $rint = random_int(0,2147483647);
          if(Ticket::withTrashed()->where('number',$rint)->count() == 0){
            break;
          }
        }
        
        $t->number = $rint;
        
        if(request()->parent !== null){
          $t->parent = $R->parent;
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
      $t->text   = $R->text;
      $t->title  = $R->title;
      $t->author = $R->author;
      $t->status = $R->status;
      $t->tags   = $R->tags;
      
      $t->files = $t->files ?? [];//Initialize if null
      $t->save();//Save because I need the ID
      
      $old_files = $R->old_files ?? [];
      $files  = $t->files;
      //If file is deleted set it to null
      foreach($files as $idx => $name){
        if(!in_array($name,$old_files)){
          $files[$idx] = null;
        }
      }
      
      $new_files = $R->file('files') ?? [];
      foreach($new_files as $nf){//Add new files
        $filenumber = count($files);
        $filename   = $nf->getClientOriginalName();
        $files[] = $filename;
        $nf->storeAs(file_path($t->number,$t->id,$filenumber),$filename);
      }
      
      $t->files = $files;
      $t->save();
      
      return $t;
    });
  });

  Route::get('/ticket_file/{number}/{id}/{fnumber}',function(int $number,int $id,int $fnumber){
    $R = request();
    $t = Ticket::withTrashed()->where('number',$R->number)
    ->where('id',$id)->first();
    if(is_null($t) || ($fnumber >= count($t->files)) || $t->files[$fnumber] === null){
      return null;
    }
    return Storage::download(file_path($number,$id,$fnumber).'/'.$t->files[$fnumber]);
  });
});

Route::delete('/delete_ticket/{number}',function(int $number){
  return DB::transaction(function() use ($number){
    $t = Ticket::where('number',$number)->first();
    if(is_null($t)) return 0;
    $is_parent = $t->parent == $t->number;
    $t->delete();
    if(!$is_parent) return 1;
    
    $children = Ticket::where('parent',$number)->orderBy('order','asc')->get();
    if($children->count() == 0) return 1;
    
    $next_parent = $children[0];
    $next_parent->parent = $next_parent->number;
    $next_parent->save();
    
    foreach($children->slice(1) as $child){
      $child->parent = $next_parent->number;
    }
    
    return 1;
  });
});

Route::delete('/delete_parent_ticket/{parent}',function(int $parent){
  return DB::transaction(function() use ($parent){
    $ts = Ticket::where('parent',$parent)->get();
    if(is_null($ts)) return 0;
    
    foreach($ts as $t) $t->delete();
    
    return 1;
  });
});

