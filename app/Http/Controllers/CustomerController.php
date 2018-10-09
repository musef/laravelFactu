<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\PaymentMethod;
use Illuminate\Support\Facades\Auth;



class CustomerController extends Controller
{
    
    public function __construct() {
        $this->middleware('auth');
    }
    
    
    /**
     * Esta función muestra un listado de los clientes de una empresa con idcompany
     * @return type
     */
    public function showListCustomers($idcompany=0) {
        
        // mensajes
        $messageWrong=$messageOK=null;
                
        // obtenemos la lista de clientes
        $customers= Customer::where('idcompany',$idcompany)
            ->orderBy('customer_name')->get();
        
        if (count($customers)<1) $messageWrong='No hay ningún cliente en la lista';
        
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
    public function createNewCustomer($idcompany=0) {

        // mensajes
        $messageWrong=$messageOK=null;
        
        // creamos un cliente en blanco
        $customer=new Customer();
        
        // obtenemos las formas de pago de la empresa
        $methods= PaymentMethod::where('idcompany',$idcompany)->get();
        
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

        //preparamos el objeto y lo grabamos
        $customer=new Customer;
        $customer->idcompany=$idcompany;
        $customer->customer_name=$name;
        $customer->customer_nif=$nif;
        $customer->customer_address=$address;
        $customer->customer_city=$city;
        $customer->customer_zip=$zip;
        $customer->idmethod=$pay;

        // mensajes
        $messageWrong=$messageOK=null;
        
        try {
            $customer->save();            
            $messageOK='Cliente grabado satisfactoriamente';
        } catch (Exception $ex) {
            $customer=new Customer;
            // mensajes
            $messageWrong='Error: no ha sido posible grabar el cliente';
        }
        
        // obtenemos las formas de pago de la empresa
        $methods= PaymentMethod::where('idcompany', Auth::guard('')->user()->idcompany)->get();        

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
        
        // leemos el objeto
        $customer= Customer::findOrFail($id);

        // mensajes
        $messageWrong=$messageOK=null;
        
        if (!is_null($customer)) {
            $messageOK='Cliente leído correctamente';            
        } else {
            $customer=new Customer;            
            // mensajes
            $messageWrong='Error: cliente inexistente';
        }
                        
        // obtenemos las formas de pago de la empresa
        $methods= PaymentMethod::where('idcompany', Auth::guard('')->user()->idcompany)->get();
        
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

        //obtenemos el objeto, lo modificamos y grabamos
        $customer= Customer::find($id);
        
        // mensajes
        $messageWrong=$messageOK=null;
        
        if (is_null($customer)) {
            $customer=new Customer;
            // obtenemos las formas de pago de la empresa
            $methods= PaymentMethod::where('idcompany', Auth::guard('')->user()->idcompany)->get();                
            // mensajes
            $messageWrong='Cliente inexistente, no ha sido posible realizar la modificación';            
        } else {
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
                // obtenemos las formas de pago de la empresa
                $methods= PaymentMethod::where('idcompany',$customer->idcompany)->get();                 
            } catch (Exception $ex) {
                $customer=new Customer;
                // obtenemos las formas de pago de la empresa
                $methods= PaymentMethod::where('idcompany', Auth::guard('')->user()->idcompany)->get();                
                // mensajes
                $messageWrong='Error: no ha sido posible modificar el cliente';
            }
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
            // error DDBB
            $messageWrong='Error: no ha sido posible eliminar el cliente';
        }
                
        // obtenemos la lista de clientes
        $customers= Customer::where('idcompany',Auth::guard('')->user()->idcompany)
            ->orderBy('customer_name')->get();
        
        if (count($customers)<1) $messageWrong='No hay ningún cliente en la lista';        
        
        return view('customers/listCustomers')
            ->with('customers',$customers)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);        
        
    }
    
}
