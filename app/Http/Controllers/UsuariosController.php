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
        //
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
    public function edit(Usuarios $usuarios)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuarios $usuarios)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuarios $usuarios)
    {
        //
    }
}
