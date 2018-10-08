<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Company;
use App\PaymentMethod;


class CompanyController extends Controller
{
               
    public function __construct() 
    {
        $this->middleware('auth');
    }
    
    
    /**
     * Esta funcion es la entrada desde el formulario para visualizar el perfil 
     * de la empresa.
     * ATENCION: Por diseño, solo hay una empresa, con el id=1
     */    
    public function showCompanyProfile() 
    {
        //obtenemos el objeto usuario autenticado
        $iduser=Auth::guard('')->user()->id;
        
        // por diseño, solo existe una empresa
        $idcompany=1;
        
        // obtenemos un objeto user en DDBB
        $company= Company::findOrFail($idcompany);
                        
        return view('company/companyProfile')
        ->with('company',$company);
    }
    
    
    /**
     * Esta función realiza el cambio de los datos del usuario del perfil,
     * según los datos del formulario.
     * 
     * Debe tener presente la contraseña fake
     * 
     * @param Request $request
     */
    public function changeCompanyProfile(Request $request,$role='user') {
        
        
        $messageWrong=$messageOK=null;
        

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
    
    
    /**
     * Esta función lista los métodos de pagos de una compañia determinad por idcompany
     * @param type $idcompany
     */
    public function listPaymentMethods($idcompany=0) {
        
        // obtenemos todos los métodos de pago
        $list= PaymentMethod::where('idcompany',$idcompany)->get();
        
        //mensajes
        $messageOK=$messageWrong=null;
        
        // mensaje de error
        if (is_null($list)) $messageWrong='No hay formas de pagos para esta empresa';
                
        return view('company/listPaymentMethods')
            ->with('pmethods',$list)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong); 
        
    }
    
    /**
     * Esta función muestra el formulario de creación de métodos de pago
     * @return type
     */
    public function createPaymentMethod() {

        $method= new PaymentMethod();
        
        return view('company/paymentProfile')
            ->with('method',$method); 
        
    }

    
    /**
     * Está función muestra el método de pago seleccionado, correspondiente al
     * parámetro id
     * @param type $id
     * @return type
     */
    public function editPaymentMethod($id=0) {
        
        // obtenemos el objeto
        $method= PaymentMethod::find($id);

        //mensajes
        $messageOK=$messageWrong=null;
            
        if (is_null($method)) {
            $method=new PaymentMethod();
            $messageWrong='No ha sido localizado ese método de pago, o no existe';
        } else {
            $messageOK='Método de pago localizado';
        }
        
        return view('company/paymentProfile')
            ->with('method',$method)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);                 
                
    }
    
    
    /**
     * Esta función recoge los datos del formulario, y cambia la información
     * en base de datos, de acuerdo con los nuevos datos introducidos
     * @param Request $request
     * @return type
     */
    public function changePaymentMethod(Request $request) {
        
        // obtenemos los datos del formulario
        $id=$request->input('methodid');
        
        $name=$request->input('methodname');
        $diff=$request->input('methoddiff');
        $day=$request->input('methodday');
        
        
        // obtenemos el objeto y lo modificamos
        $method= PaymentMethod::find($id);
        $method->payment_method=$name;
        $method->diff=$diff;
        $method->payment_day=$day;

        $ret=$method->save();
        
        //mensajes
        $messageOK=$messageWrong=null;
                
        if ($ret===false || $ret==0) {
            $method=new PaymentMethod();
            $messageWrong='No ha sido posible modificar ese método de pago, o no existe';
        } else {
            $messageOK='Método de pago modificado correctamente';
        }
        
        return view('company/paymentProfile')
            ->with('method',$method)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);                 
                
    }


    /**
     * Esta función recoge los datos del formulario, y crea una forma de pago
     * en base de datos, de acuerdo con los nuevos datos introducidos
     * @param Request $request
     * @return type
     */
    public function recordNewPaymentMethod(Request $request) {
        
        // obtenemos los datos del formulario
        $idcompany=$request->input('companyid');        
        $name=$request->input('methodname');
        $diff=$request->input('methoddiff');
        $day=$request->input('methodday');
        
        
        // obtenemos el objeto y lo modificamos
        $method= new PaymentMethod;
        $method->idcompany=$idcompany;
        $method->payment_method=$name;
        $method->diff=$diff;
        $method->payment_day=$day;

        $ret=$method->save();
        
        //mensajes
        $messageOK=$messageWrong=null;
                
        if ($ret===true || $ret==1) {
            $messageOK='Método de pago creado correctamente';            
        } else {
            $method=new PaymentMethod();
            $messageWrong='No ha sido posible crear ese método de pago';
        }
        
        return view('company/paymentProfile')
            ->with('method',$method)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);                 
                
    }

    
    /**
     * Está función elimina el método de pago seleccionado, correspondiente al
     * parámetro id
     * @param type $id
     * @return type
     */
    public function deletePaymentMethod($id=0) {
        
        // borramos el objeto
        $ret= PaymentMethod::destroy($id);

        //mensajes
        $messageOK=$messageWrong=null;
            
        if ($ret===true || $ret==1) {
            // borrado OK
            $method=new PaymentMethod();            
            $messageOK='Método de pago eliminado correctamente';
        } else {
            // no ha sido posible borrar el objeto, lo retornamos
            
            // obtenemos el objeto
            $method= PaymentMethod::find($id);

            if (is_null($method)) {
                $method=new PaymentMethod();
                $messageWrong='No ha sido localizado ese método de pago, o no existe';
            } else $messageWrong='No ha sido posible eliminar ese método de pago, o no existe';
        }
        
        return view('company/paymentProfile')
            ->with('method',$method)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);                 
                
    }    
    
    
}

