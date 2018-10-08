<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    
}
