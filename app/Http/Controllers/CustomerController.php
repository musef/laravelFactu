<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

use App\Customer;
use App\PaymentMethod;

class CustomerController extends Controller
{
    
    public function __construct() {
        $this->middleware('auth');
    }
    
    
    /**
     * Esta función muestra un listado de los clientes de una empresa con idcompany
     * @return type
     */
    public function showListCustomers() {
        
        // mensajes
        $messageWrong=$messageOK=null;
        
        // leemos la compañia del usuario
        $idcompany=Auth::guard('')->user()->idcompany;
                
        try {
            // obtenemos la lista de clientes
            $customers= Customer::where('idcompany',$idcompany)
                ->orderBy('customer_name')->get();  
            if (is_null($customers) || count($customers)<1) $messageWrong='No hay ningún cliente en la lista';        
        } catch (Exception $ex) {
            $customers=null;
            $messageWrong='Error leyendo la lista de clientes';
        } catch (QueryException $quex) {
            $customers=null;
            $messageWrong='Error en Base de Datos leyendo la lista de clientes';
        }
        
        return view('customers/listCustomers')
            ->with('customers',$customers)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);                 
    }
    
    
    /**
     * Esta funcion crea el formulario para crear un nuevo cliente de la empresa $idcompany
     * @param type $idcompany
     * @return type
     */
    public function createNewCustomer() {

        // mensajes
        $messageWrong=$messageOK=null;
        
        // creamos un cliente en blanco
        $customer=new Customer();
        
        // leemos la compañia del usuario
        $idcompany=Auth::guard('')->user()->idcompany;
        
        try {
            // obtenemos las formas de pago de la empresa
            $methods= PaymentMethod::where('idcompany',$idcompany)->get();            
        } catch (Exception $ex) {
            $customers=null;
            $messageWrong='Error leyendo la lista de forma de pagos';
        } catch (QueryException $quex) {
            $customers=null;
            $messageWrong='Error en Base de Datos leyendo la lista formas de pagos';
        }
        
        // mostramos formulario
        return view('customers/customerProfile')
            ->with('customer',$customer)
            ->with('methods',$methods);        
        
    }
    
    
    /**
     * Esta función crea un nuevo cliente con los datos recibidos del formulario
     * @param Request $request
     * @return type
     */
    public function recordNewCustomer(Request $request) {
        
        // leemos el formulario
        $idcompany= clearInput($request->input('companyid'));
        $name= clearInput($request->input('customername'));
        $nif= clearInput($request->input('customernif'));
        $address= clearInput($request->input('customeraddress'));
        $city= clearInput($request->input('customercity'));
        $zip= clearInput($request->input('customerzip'));
        $pay= clearInput($request->input('customerpayment'));

        // mensajes
        $messageWrong=$messageOK=null;        
                
        // leemos la compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // comprobamos la pertenencia del usuario a la empresa
        if ($idcompany == $idcomp) {
            
            try {
                //preparamos el objeto y lo grabamos
                $customer=new Customer;
                $customer->idcompany=$idcompany;
                $customer->customer_name=$name;
                $customer->customer_nif=$nif;
                $customer->customer_address=$address;
                $customer->customer_city=$city;
                $customer->customer_zip=$zip;
                $customer->idmethod=$pay;
                
                $customer->save();            
                $messageOK='Cliente grabado satisfactoriamente';
                
                
            } catch (Exception $ex) {
                $customer=new Customer;
                // mensajes
                $messageWrong='Error: no ha sido posible grabar el cliente';
            } catch (QueryException $quex) {
                $customer=new Customer;                
                    // error Dato
                $messageWrong='Error: no ha sido posible grabar el cliente';
            }

            try {
                // obtenemos las formas de pago de la empresa
                $methods= PaymentMethod::where('idcompany',$idcomp)->get();            
            } catch (Exception $ex) {
                $methods=null;
                $messageWrong='Error leyendo la lista de forma de pagos';
            } catch (QueryException $quex) {
                $methods=null;
                $messageWrong='Error en Base de Datos leyendo la lista formas de pagos';
            }             
            
        } else {
            $messageWrong='El usuario no pertenece a la empresa';
            $customer=new Customer;
            $methods=null;
        }
                
        return view('customers/customerProfile')
            ->with('customer',$customer)
            ->with('methods',$methods)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);  
        
    }
    
    
    /**
     * Esta función muestra por pantalla el cliente seleccionado correspondiente
     * al parámetro $id
     * @param $id
     * @return type
     */
    public function editCustomer($id=0) {

        // mensajes
        $messageWrong=$messageOK=null;
        
        // leemos el objeto
        $customer= Customer::findOrFail($id);

        if (!is_null($customer)) {
            $messageOK='Cliente leído correctamente';            
        } else {
            $customer=new Customer;            
            // mensajes
            $messageWrong='Error: cliente inexistente';
        }        
                
        // leemos la compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // comprobamos la pertenencia del usuario a la empresa
        if ($customer->idcompany == $idcomp) {
        
            try {
                // obtenemos las formas de pago de la empresa
                $methods= PaymentMethod::where('idcompany',$idcomp)->get();            
            } catch (Exception $ex) {
                $methods=null;
                $messageWrong='Error leyendo la lista de forma de pagos';
            } catch (QueryException $quex) {
                $methods=null;
                $messageWrong='Error en Base de Datos leyendo la lista formas de pagos';
            }            
            
        } else {
            // el usuario no pertenece a la empresa
            // por tanto, no mostramos el cliente
            $messageWrong='El usuario no pertenece a la empresa';
            $customer=new Customer;
            $methods=null;
        }            

        return view('customers/customerProfile')
            ->with('customer',$customer)
            ->with('methods',$methods)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);  
        
    }
    
    
    /**
     * Esta función modifica un cliente con los datos recibidos del formulario
     * @param Request $request
     * @return type
     */
    public function changeCustomer(Request $request) {
        
        // leemos el formulario
        $id= clearInput($request->input('customerid'));        

        $name= clearInput($request->input('customername'));
        $nif= clearInput($request->input('customernif'));
        $address= clearInput($request->input('customeraddress'));
        $city= clearInput($request->input('customercity'));
        $zip= clearInput($request->input('customerzip'));
        $pay= clearInput($request->input('customerpayment'));

        // mensajes
        $messageWrong=$messageOK=null;

        // leemos la compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;         
        
        //obtenemos el objeto, lo modificamos y grabamos
        $customer= Customer::findOrFail($id);
        
        // comprobamos si existe el cliente a modificar
        if (is_null($customer)) {
            $customer=new Customer;
            // mensajes
            $messageWrong='Cliente inexistente, no ha sido posible realizar la modificación';             
        } else {
            // comprobamos la pertenencia del usuario a la empresa
            if ($customer->idcompany == $idcomp) {
                // modificamos
                $customer->customer_name=$name;
                $customer->customer_nif=$nif;
                $customer->customer_address=$address;
                $customer->customer_city=$city;
                $customer->customer_zip=$zip;
                $customer->idmethod=$pay;       

                try {
                     $customer->save();            
                     $messageOK='Cliente modificado satisfactoriamente';                 
                } catch (Exception $ex) {
                     $customer=new Customer;              
                     // mensajes
                     $messageWrong='Error: no ha sido posible modificar el cliente';
                } catch (QueryException $quex) {
                    $customer=new Customer;
                     // error Dato
                     $messageWrong='Error: no ha sido posible modificar el cliente';
                }       

            } else {
                 // el usuario no pertenece a la empresa
                 // por tanto, no mostramos el cliente
                 $messageWrong='El usuario no pertenece a la empresa';
                 $customer=new Customer;
                 $methods=null;

            }  
        }
        
       
        try {
            // obtenemos las formas de pago de la empresa
            $methods= PaymentMethod::where('idcompany',$idcomp)->get();            
        } catch (Exception $ex) {
            $methods=null;
            $messageWrong='Error leyendo la lista de forma de pagos';
        } catch (QueryException $quex) {
            $methods=null;
            $messageWrong='Error en Base de Datos leyendo la lista formas de pagos';
        }          
                

        return view('customers/customerProfile')
            ->with('customer',$customer)
            ->with('methods',$methods)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);          
    }
    
    
    /**
     * Esta función borra el cliente correspondiente al parámetro $id
     * y muestra el listado de clientes restantes
     * @param type $id
     * @return type
     */
    public function deleteCustomer($id=0) {
        
        // mensajes
        $messageWrong=$messageOK=null;     
        
        // leemos la compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        // obtenemos el cliente
        $customer= Customer::findOrFail($id);
        
        // comprobamos la pertenencia del usuario a la empresa
        if ($customer->idcompany == $idcomp) {

            try {
                // borramos el cliente
                $res= Customer::destroy($id);

                if ($res===true || $res==1) {
                    // todo OK
                    $messageOK='Cliente eliminado satisfactoriamente';
                } else {
                    // cliente inexistente
                    $messageWrong='Cliente inexistente: no ha sido posible eliminar el cliente';
                }
                              
            } catch (Exception $ex) {
                // error Dato
                $messageWrong='Error: no ha sido posible eliminar el cliente';
            } catch (QueryException $quex) {
                // error Dato
                $messageWrong='Error: imposible eliminar el cliente porque tiene operaciones registradas en base de datos.';
            }            
            
            try {
                // obtenemos la lista de clientes
                $customers= Customer::where('idcompany',$idcomp)
                    ->orderBy('customer_name')->get();
        
                if (count($customers)<1) $messageWrong='No hay ningún cliente en la lista'; 
                              
            } catch (Exception $ex) {
                // error Dato
                $messageWrong='Error: no ha sido posible obtener la lista de clientes';
                $customers=null;
            } catch (QueryException $quex) {
                // error Dato
                $messageWrong='Error de base de datos: no ha sido posible obtener la lista de clientes';
                $customers=null;
            }            

        } else {
             // el usuario no pertenece a la empresa
             // por tanto, no mostramos el cliente
            $messageWrong='El usuario no pertenece a la empresa';
            $customers=null;

        }  
        
        return view('customers/listCustomers')
            ->with('customers',$customers)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);        
        
    }
    
    
    /**
     * Esta función muestra el formulario de obtención de listados de clientes 
     * @param type $id
     * @return type
     */
    public function showCustomersListBySelection($id=0) {
        
        // mensajes
        $messageWrong=$messageOK=null;
        
        // parametros de busqueda
        $parameters=array('name'=>'','city'=>'','zip'=>'','selected'=>0);
        
        // leemos la compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        try {
            // obtenemos las formas de pago de la empresa
            $methods= PaymentMethod::where('idcompany', $idcomp)
                ->get();         
        } catch (Exception $ex) {
            $methods=null;
            $messageWrong='Error leyendo la lista de pagos';
        } catch (QueryException $quex) {
            $methods=null;
            $messageWrong='Error en Base de Datos leyendo la lista de pagos';
        }        
           
        return view('customers/customersListBySelection')
            ->with('parameters',$parameters)
            ->with('paymentMethods',$methods)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);  
        
    }
    
    /**
     * Esta función busca en base de datos y muestra, los clientes que se han
     * seleccionado mediante el formulario
     * @param Request $request
     * @return type
     */
    public function locateCustomersByOptions(Request $request) {
        
        // mensajes
        $messageWrong=$messageOK=null;

        // leemos los parametros del formulario
        $idcompany= clearInput($request->input('companyid'));
        $name= clearInput($request->input('name'));
        $city= clearInput($request->input('city'));
        $zip= clearInput($request->input('zip'));
        $pmethod= clearInput($request->input('paymentMethod'));    
        
        // leemos la compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        // parametros de busqueda
        $parameters=array('name'=>$name,'city'=>$city,'zip'=>$zip,'selected'=>$pmethod);
        
        ($pmethod==0) ? $pmethod='_' : $pmethod=$pmethod;
        
        // comprobamos la pertenencia del usuario a la empresa
        if ($idcompany == $idcomp) {  
        
            try {
                $customers= Customer::where([
                    ['idcompany',$idcomp],
                    ['customer_name','like','%'.$name.'%'],
                    ['customer_zip','like','%'.$zip.'%'],
                    ['customer_city','like','%'.$city.'%'],
                    ['idmethod','like', $pmethod],
                ])->get();
            } catch (Exception $ex) {
                $customers=null;
                $messageWrong='Error buscando los clientes por formulario';
            } catch (QueryException $quex) {
                $customers=null;
                $messageWrong='Error en Base de Datos buscando los clientes por formulario';
            }             
  
        } else {
             // el usuario no pertenece a la empresa
             // por tanto, no mostramos la lista
            $messageWrong='El usuario no pertenece a la empresa';
            $customers=null;

        }            
        
        
        try {
            // obtenemos las formas de pago de la empresa
            $methods= PaymentMethod::where('idcompany',$idcomp)->get();            
        } catch (Exception $ex) {
            $methods=null;
            $messageWrong='Error leyendo la lista de forma de pagos';
        } catch (QueryException $quex) {
            $methods=null;
            $messageWrong='Error en Base de Datos leyendo la lista formas de pagos';
        }         
        
        return view('customers/customersListBySelection')
            ->with('parameters',$parameters)
            ->with('customers',$customers)
            ->with('paymentMethods',$methods)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);         
        
    }
    
}
