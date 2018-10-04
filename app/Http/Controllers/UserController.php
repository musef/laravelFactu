<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserController extends Controller
{
    
    // contraseña falsa para mostrar en formulario
    private $PASSWORD_FAKE='XXXXXXXXXXXXXXX';    
    
    
    public function __construct() 
    {
        $this->middleware('auth');
    }
    
    
    /**
     * Esta funcion es la entrada desde el formulario para visualizar el perfil 
     * del usuario 
     */    
    public function showUserProfile() 
    {
        //obtenemos el objeto usuario autenticado
        $iduser=Auth::guard('')->user()->id;
        
        // obtenemos un objeto user en DDBB
        $user= User::findOrFail($iduser);
        
        // para no mostrar la contraseña, se envía una contraseña fake
        $user->password=$this->PASSWORD_FAKE;        
        
        return view('users/userProfile')
        ->with('user',$user);
    }
    
    
    /**
     * Esta función realiza el cambio de los datos del usuario del perfil,
     * según los datos del formulario.
     * 
     * Debe tener presente la contraseña fake
     * 
     * @param Request $request
     */
    public function changeUserProfile(Request $request,$role='user') {
        
        
        $messageWrong=$messageOK='';
        

        // obtenemos el id del usuario a modificar
        $iduser=$request->input('userid');
        //creamos el objeto
        $user=User::find($iduser);

        if (is_null($user)) {
            $messageWrong='Se ha producido un error modificando el usuario';
        } else {
            // leemos los campos del formulario que puede cambiar
            $name=trim($request->input('username'));
            $email=trim($request->input('useremail'));
            $pass=trim($request->input('userpass'));

            // si el nombre tiene la longitud adecuada lo cambia
            if (strlen($name)>2 && strlen($name)<101) $user->name=$name;
            else $messageWrong.='Longitud inadecuada de nombre. No se modifica.<br />';

            if (strlen($email)>6 && strlen($email)<256) $user->email=$email;
            else $messageWrong.='Longitud inadecuada de email. No se modifica.<br />';

            //contemplamos la password fake
            if (strlen($pass)>7 && strlen($pass)<16 && $pass!==$this->PASSWORD_FAKE) $user->password= Hash::make($pass);
            else $messageWrong.='Longitud inadecuada de password. No se modifica.<br />';            

            // grabamos
            $result=$user->save();
            
            // para no mostrar la contraseña, se envía una contraseña fake a la vista
            $user->password=$this->PASSWORD_FAKE;

            if ($result===1 || $result==true) {
                $messageOK='Los datos del formulario han sido grabados.';
            } else {
                $messageWrong='Error grabando los datos del usuario. No se ha grabado ningún dato.';                
            }                
        }
                
        return view('users/userProfile')
            ->with('user',$user)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);            
        
    }
    
    
}
