<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

use App\User;
use App\Company;


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
        
        // obtenemos el nombre de la compañia
        $companyName=Company::findOrFail($user->idcompany)->company_name;
        
        // para no mostrar la contraseña, se envía una contraseña fake
        $user->password=$this->PASSWORD_FAKE;        
        
        return view('users/userProfile')
            ->with('companyName',$companyName)
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
    public function changeUserProfile(Request $request) {
        
        
        $messageWrong=$messageOK='';
        

        // obtenemos el id del usuario a modificar
        $iduser=$request->input('userid');
        //obtenemos el objeto
        $user=User::find($iduser);

        if (is_null($user)) {
            $messageWrong='Se ha producido un error modificando el usuario';
            $companyName='';
            $user=new User;            
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

            try {
            
                // grabamos
                $result=$user->save();     
                
            } catch (Exception $ex) {
                $user=new User;
                $messageWrong='Error grabando los datos del usuario. No se ha grabado ningún dato.';                 
            } catch (QueryException $quex) {
                $user=new User;
                $messageWrong='Error de base de datos grabando los datos del usuario.';
            }

            try {
                // obtenemos el nombre de la compañia
                $companyName=Company::find($user->idcompany)->company_name;                
            } catch (Exception $ex) {
                $companyName='';

            } catch (QueryException $quex) {
                $companyName='';                
            }
            
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
            ->with('companyName',$companyName)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);            
        
    }
    
    
}
