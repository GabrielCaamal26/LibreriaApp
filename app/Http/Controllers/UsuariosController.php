<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    //Funcion que valida que solamente un usuario autenticado puede tene acceso
    public function _construct(){
        $this->middleware('auth');  
    }
    //Funcion que se ejecuta cuando se mandar a llamar a la ruta MiPerfil
    public function MiPerfil(){
        return view('modulos.MiPerfil');
    }
    public function MiPerfilUpdate(Request $request)
        {
        //verificar si el coreo actual es diferente al correo enviado por el formulario
        //lo que significa que se quiere actualizar
        if(auth()->user()->email != request('email')){
            //se requiere actualizar la contraseña
            if(request('passwordN')){
                //se crea un array con los datos validados
                //SI LOS DATOS NOS SE VALIDAD, NO SE ACTUALIZA
                $datos=request()->validate([
                    'name'=> ['required','string','max:255'],
                    'email'=> ['required','email','unique:users'],
                    'passwordN'=> ['required','string','min:3']
                ]);
            }else{
                $datos = request()-> validate([
                    'name'=> ['required','string','max:255'],
                    'email'=> ['required','email','unique:users']
                ]);
            }
        }else{
            if(request('passwordN')){
                $datos = request()->validate([
                    'name'=> ['required','string','max:255'],
                    'email'=> ['required','email'],
                    'passwordN'=> ['required','string','min:3']
                ]);
            }else{
                $datos = request()-> validate([
                    'name'=> ['required','string','max:255'],
                    'email'=> ['required','email']
                ]);
            }
        }
        //si se quiere actualizar el documento
        if(request('documento')){
            $documento = $request['documento'];
        }else{
            $documento = auth()->user()->documento;
        }
        //si se quiere actualizar foto
        if(request('fotoPerfil')){
            Storage::delete('public/'.auth()->user()->foto);  
            $rutaImg = $request['fotoPerfil']->store('usuarios/'.$datos["name"],'public');
        }else{
            $rutaImg = auth()->user()->foto;
        }

        //Cambiar contraseña y cumple con la regla 
        if(isset($datos["passwordN"])){
            DB::table('users')->where('id',auth()->user()->id)->update(['name'=>$datos["name"],
            'email'=>$datos["email"],
            'documento'=>$documento,
            'foto'=>$rutaImg,
            'password'=> Hash::make(request("passwordN"))]);
        }else{
            DB::table('users')->where('id',auth()->user()->id)->update(['name'=>$datos["name"],
            'email'=>$datos["email"],
            'documento'=>$documento,
            'foto'=>$rutaImg]);
        }
        
        //Despues de actualizar redireccionamos a la misma vista  "MiPerfil"
        return redirect('MiPerfil');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = Usuarios::all();
        return view('modulos.Usuarios')->with('usuarios', $usuarios );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       //Validar los datos recibidos
       $datos = request()->validate([
        'name' => ['string','max:255'],
        'rol' => ['required'],
        'email' => ['string', 'unique:users'],
        'password' => ['string','min:3']
    ]);
    
    //Crear el registro en la tabla users en la base de datos 
    Usuarios::create([
        'name' => $datos['name'],
        'email' => $datos['email'],
        'rol' => $datos['rol'],
        'password'=> Hash::make($datos['password']),
        'documento' => '',
        'foto' => ''
    ]);

    //redireccionamos a la vista de usuarios, al llamar a la ruta usuarios
    return redirect('Usuarios')->with('UsuarioCreado','OK');

    }

    /**
     * Display the specified resource.
     */
    public function show(Usuarios $usuarios)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usuarios $id)
    {
           if(auth()->user()->rol != 'Administrador'){
            return redirect('Inicio');
        }
        $usuarios = Usuarios::all();
        $usuario = Usuarios::find($id->id);
        return view('modulos.Usuarios',compact('usuarios','usuario'));
    }
    /**
    * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Usuarios  $usuarios
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuarios::find($id);
            if($usuario["email"]!= request('email')){
                $datos=request()->validate([
                    'name'=>['required'],
                    'rol'=>['required'],
                    'email'=>['required','email','unique:users']
                ]);
            }else{
                $datos=request()->validate([
                    'name'=>['required'],
                    'rol'=>['required'],
                    'email'=>['required','email']
                ]);
            }
            if($usuario["password"]!=request('password')){
                $clave=request("password");
            }else{
                $clave=$usuario["password"];
            }
            DB::table('users')->where('id',$usuario['id'])->update(['name'=>$datos["name"],'email'=>$datos["email"],'rol'=>$datos['rol'],'password'=>Hash::make($clave)]);

            return redirect('Usuarios');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Usuarios  $usuarios
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $usuario = Usuarios::find($id);
        $exp = explode("/", $usuario->foto);

    if(Storage::delete('public/'.$usuario->foto)){
        Storage::deleteDirectory('public/'.$exp[0].'/'.$exp[1]);
    }
        Usuarios::destroy($id);
        return redirect('Usuarios');
    }
}
