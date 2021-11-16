<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use App\User; 
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\Inscripcion;

class AuthController extends Controller 
{
  
   private $apiToken;
   public function __construct()
    {
    $this->apiToken = uniqid(base64_encode(Str::random(40)));
    }
  /** 
   * 
   * @return \Illuminate\Http\Response 
   */ 

   public function updateOrCreateUser($request){
    $toUpdate=[];
    $toUpdate['password']=bcrypt($request->dni);
    if($request->nombres){
      $toUpdate['nombres' ]=$request->nombres;
    }
    if($request->apellidos){
      $toUpdate['apellidos' ]=$request->apellidos;
    }
    if($request->email){
      $toUpdate['email' ]=$request->email;
    }
    if($request->telefono){
      $toUpdate['telefono' ]=$request->telefono;
    }
    $toUpdate['usodatos' ]=true;

    User::updateOrCreate(
      ['username' => $request->dni],
      $toUpdate
    );
   }
  
  public function login(Request $request){ 
    $request->validate([
        'dni' => 'required|string',
        'nombres' => 'string|nullable',
        'apellidos' => 'string|nullable',
        'email' => 'string|nullable',
        'telefono' => 'string|nullable',
        'usodatos' => 'nullable'
    ]);
    $this->updateOrCreateUser($request);
    if(Auth::attempt(['username' => $request->dni, 'password' => $request->dni])){ 
        $user = Auth::user(); 
        $user->api_token=$this->apiToken;
        $user->save();
        $success['token'] = $this->apiToken;
        $success['name'] =  $user->nombres;
        return response()->json([
            'message' => 'Bienvenido!',
            'data' => $success
        ]); 
    } else { 
      return response()->json([
        'status' => 'error',
        'data' => 'Unauthorized Access'
      ]); 
    } 
  }

  public function signUp(Request $request)
  {
      $request->validate([
          'dni' => 'required|string',
          'nombres' => 'string|nullable',
          'apellidos' => 'string|nullable',
          'email' => 'string|nullable',
          'telefono' => 'string|nullable',
          'usodatos' => 'nullable'
      ]);

      $participant=User::where('username',$request->dni)->first();
      if($participant){
        $this->updateOrCreateUser($request);
      }else{
        $this->updateOrCreateUser($request);

        if($request->email){
          $afiliacion = new \stdClass();
          $afiliacion->receiver = $request->email;
          Mail::to($request->email)->send(new Inscripcion($afiliacion));
        }
      }

      return response()->json([
        'message' => 'Registro exitoso!'
      ], 201);
  }

  public function logout(Request $request)
  {
    $user = Auth::user(); 
    $user->api_token=null;
    $user->save();

      return response()->json([
        'message' => 'Sesión cerrada!'
      ]);
  }

  public function checkExistingUser(Request $request){ 
    $request->validate([
        'dni' => 'required|string',
    ]);
    $userFound=User::where('username', $request->dni)->first();
    
    return response()->json([
      // 'message' => $userFound ? "Ya está registrado, ingrese." : "Todavía no está registrado, complete los campos.",
      'message' => $userFound ? "Ya está registrado, actualiza tus datos." : "Todavía no está registrado, complete los campos.",
      'exists' => $userFound ? true : false,
      'user' => $userFound
    ]);
    
  }

  public function sendMail(Request $request){
    $afiliacion = new \stdClass();
    $emails = ['yodanielayo@gmail.com'];
    
    foreach ($emails as $email) {
      $afiliacion->receiver = $email;
      Mail::to($email)->send(new Inscripcion($afiliacion));
    }

    return response()->json([
      'message' => "correo enviado!"
    ]);
    
  }
}