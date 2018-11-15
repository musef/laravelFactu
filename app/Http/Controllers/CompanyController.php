<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

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
        
        // por diseño, cada usuario solo puede acceder a una empresa
        $idcompany=Auth::guard('')->user()->idcompany;
        
        // obtenemos un objeto company en DDBB
        $company= Company::findOrFail($idcompany);
                        
        return view('company/companyProfile')
            ->with('company',$company);
    }
    
    
    /**
     * Esta función realiza el cambio de los datos de la empresa
     * según los datos del formulario.
     * 
     * @param Request $request
     */
    public function changeCompanyProfile(Request $request) {
        
        // mensajes
        $messageWrong=$messageOK=null;

        // leemos los campos del formulario que puede cambiar
        $idcompany=trim($request->input('companyid'));
        $name=trim($request->input('companyname'));
        $nif=trim($request->input('companynif'));
        $address=trim($request->input('companyaddress'));
        $city=trim($request->input('companycity'));
        $zip=trim($request->input('companyzip'));            
        
        //obtenemos el objeto usuario autenticado
        $idcomp=Auth::guard('')->user()->idcompany;
        
        if ($idcompany == $idcomp) {

            // obtenemos la empresa a modificar
            $company= Company::find($idcompany);
            
            if (!is_null($company)) {
                // correcto
                try {   
                    // modificamos con los datos del formulario
                    $company->company_name=$name;
                    $company->company_nif=$nif;
                    $company->company_address=$address;
                    $company->company_city=$city;
                    $company->company_zip=$zip;
                    
                    // grabamos
                    $company->save();
                    
                    $messageOK='Empresa modificada correctamente';
                    
                } catch (Exception $ex) {
                    // generamos un objeto en blanco
                    $company=new Company;
                    $messageWrong='Error modificando los datos de empresa';
                } catch (QueryException $quex) {
                    // generamos un objeto en blanco
                    $company=new Company;
                    $messageWrong='Error en base de datos modificando los datos de empresa - Error QE001';
                }  
            } else {
                // la empresa no existe
                // generamos un objeto en blanco
                $messageWrong='Empresa inexistente';                
            }
            
        } else {
            $messageWrong='Empresa no corresponde al usuario';
            // generamos un objeto en blanco
            $company=new Company;
        } 
        
        return view('company/companyProfile')
            ->with('company',$company)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);            
        
    }
    
    
    /**
     * Esta función lista los métodos de pagos de una compañia determinad por idcompany
     * @param type $idcompany
     */
    public function listPaymentMethods() {
        
        //obtenemos el objeto usuario autenticado
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // obtenemos todos los métodos de pago
        $list= PaymentMethod::where('idcompany',$idcomp)->get();
        
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

                
        //mensajes
        $messageOK=$messageWrong=null;        
        
        try {
            $ret=$method->save();    
            $messageOK='Método de pago modificado correctamente';
        } catch (Exception $ex) {
            $method=new PaymentMethod();
            $messageWrong='No ha sido posible modificar ese método de pago, o no existe';
        } catch (QueryException $quex) {
            $method=new PaymentMethod();
            $messageWrong='No ha sido posible modificar ese método de pago, o no existe- Error QE002';  
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

        
        //mensajes
        $messageOK=$messageWrong=null;
        
        try {
            $ret=$method->save();   
            $messageOK='Método de pago creado correctamente';
        } catch (Exception $ex) {
            $method=new PaymentMethod();
            $messageWrong='No ha sido posible crear ese método de pago, o no existe';
        } catch (QueryException $quex) {
            $method=new PaymentMethod();
            $messageWrong='No ha sido posible crear ese método de pago, o no existe- Error QE002';  
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
    
    
    /**
     * Esta función muestra los settings de algunos elementos configurables de la 
     * aplicación, correspondientes a la empresa con $id
     * @param type $id
     * @return type
     */
    public function settings() {
        
        //obtenemos el objeto usuario autenticado
        $idcomp=Auth::guard('')->user()->idcompany;

        // obtenemos un objeto company en DDBB
        $company= Company::findOrFail($idcomp); 
        
        // mostramos la vista
        return view('company/companySettings')
            ->with('company',$company);            
   
    }
}

