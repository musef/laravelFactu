<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

use App\Company;
use App\PaymentMethod;
use App\Config;


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
     * Esta función muestra los settings de los elementos configurables de la 
     * aplicación, correspondientes a la empresa con $id
     * @param type $id
     * @return type
     */
    public function settings(Request $request) {
        
        //mensajes
        $messageOK=$messageWrong=null;
        
        //obtenemos el objeto usuario autenticado
        $idcomp=Auth::guard('')->user()->idcompany;

        //obtenemos el id de la compañia
        $idcompany= clearInput($request->input('companyid'));
                    
        // obtenemos las configuraciones de empresa
        $configs= Config::where([
            ['idcompany',$idcomp]
        ])->get();
        
        // recuperamos la configuración en forma de array
        // para enviar a la vista
        $settings=array();
        foreach ($configs as $config =>$value) {
            $settings[$value['name']]=$value['value'];            
        }

        $messageOK='Configuraciones obtenidas';
  
        
        // mostramos la vista
        return view('company/companySettings')
            ->with('settings',$settings)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);                
   
    }


    public function updateSettings(Request $request) {
        
        //mensajes
        $messageOK=$messageWrong=null;
        
        //obtenemos el objeto usuario autenticado
        $idcomp=Auth::guard('')->user()->idcompany;

        //obtenemos el id de la compañia
        $idcompany= clearInput($request->input('companyid'));
        
        // obtenemos el resto de valores de configuraciones
        
        // SI habilita la creación de usuarios, NO la deshabilita - por defecto es NO
        (clearInput($request->input('createUsers'))=='Si') ? $createuser='Si'  : $createuser='No' ; 
        // SI habilita la creación de roles, NO la deshabilita - por defecto es NO
        (clearInput($request->input('usingRoles'))=='Si') ? $roles='Si'  : $roles='No' ;
        // SI habilita la creación de empresa, NO la deshabilita - por defecto es NO
        (clearInput($request->input('createCompanies'))=='Si') ? $createcomp='Si'  : $createcomp='No' ; 
        // modo de crear albaranes: 1 es albarán por artículo, 2 es albarán multiartículo - por defecto es 1
        (clearInput($request->input('workmode'))=='2') ? $wmode=2  : $wmode=1 ;
        // prefijo de albaranes. maximo tamaño es 3
        $wprefix= substr(clearInput($request->input('workPrefix')), 0,3);
        // prefijo de comienzo de numero - 1 es comienzo aaaamm , 2 es ningún prefijo (0000) - por defecto es 1
        (clearInput($request->input('worksprefix2'))=='2') ? $wprefixnum=2  : $wprefixnum=1 ;     
        // longitud de número de albarán. entre 10 y 20 - 15 por defecto
        $wlength= substr(clearInput($request->input('worknumLength')), 0,2);
        if ($wlength < 10 || $wlength > 20) $wlength=15;

        // Serie de la factura (max 3 chars) - empty por defecto
        $iserial= substr(clearInput($request->input('invoicesSerial')), 0,3);        
        // prefijo de comienzo de numero factura - 1 es comienzo aaaamm , 2 es ningún prefijo (0000) - por defecto es 1
        (clearInput($request->input('invoicePrefix'))=='2') ? $iprefix=2  : $iprefix=1 ;    
        // longitud de número de albarán. entre 10 y 20 - 15 por defecto
        $ilength= substr(clearInput($request->input('invoicesLength')), 0,2);
        if ($ilength < 10 || $ilength > 20) $ilength=15;        
        // nota al pie de la factura -
        $inote= substr(clearInput($request->input('invoiceNote')), 0,255);
        
        // comprobamos la pertenencia del usuario a la empresa que modifica
        if ($idcomp == $idcompany) {
            
            // obtenemos las configuraciones de empresa
            $configs= Config::where([
                ['idcompany',$idcomp]
            ])->get();
            
            // de momento, en la version lite, solo se permiten modificar 
            // algunos parámetros, siendo los demás fijos
            foreach ($configs as $config) {
                if ($config->name=='invoiceNote') {
                    // cambiamos la nota al pie de factura
                    $configW= Config::find($config->id);
                    $configW->value=$inote;
                    $configW->save();                    
                }
                if ($config->name=='invoicePrefix') {
                    // cambiamos el prefijo de factura
                    $configW= Config::find($config->id);
                    $configW->value=$iprefix;
                    $configW->save();                    
                }                                  
                
            }
            
            $messageOK='Configuraciones modificadas';
            
            // obtenemos las configuraciones de empresa
            $configs= Config::where([
                ['idcompany',$idcomp]
            ])->get();

            // recuperamos la configuración en forma de array
            // para enviar a la vista
            $settings=array();
            foreach ($configs as $config =>$value) {
                $settings[$value['name']]=$value['value'];            
            }            
            
        } else {
            $messageWrong='El usuario no pertenece a la empresa';
            $settings=array();
        }
        
        
        // mostramos la vista
        return view('company/companySettings')
            ->with('settings',$settings)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);                
   
    }
    
    
}

