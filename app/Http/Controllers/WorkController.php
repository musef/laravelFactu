<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Work;
use App\Customer;
use App\IvaRates;



class WorkController extends Controller
{
    
    public function __construct() {
        $this->middleware('auth');
    }
    
    public function showWork(Request $request, $id=0) {
    
        // obtenemos el customerid, si se ha seleccionado en el select
        // esto no existe cuando entramos en el formulario
        if ($request->has('customerid')) {
            $customerid= clearInput($request->input('customerid'));
            ($customerid>0) ? $customer=Customer::find($customerid) : $customer=new Customer;
        } else {
          $customer=new Customer;  
        }
         
        
        $work=new Work();
        $work->work_typeiva=21;
        $work->work_qtt=1.00;
        $work->work_price=0.00;
        $work->work_date= date('d-m-Y');

        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // obtenemos los ivas activos
        $ivaRates= IvaRates::where([
            ['idcompany',$idcomp],
            ['active',true]
        ])->get();
        
        // obtenemos los clientes de la empresa
        $customers= Customer::where('idcompany',$idcomp)
                ->get();
        
        return view('works/work')
            ->with('ivaRates',$ivaRates)
            ->with('customerSelected',$customer)                
            ->with('customers',$customers)
            ->with('work',$work);
        
    }
    
 
    /**
     * Esta función graba un nuevo albarán, conforme a los datos enviados por
     * formulario.
     * Estos datos son previamente verificados y comprobados.
     * 
     * @param Request $request
     * @return type
     */
    public function recordNewWork( Request $request) {
        
        // control de error
        $error=false;
        
        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idwork= clearInput($request->input('workid'));
        $idcustomer= clearInput($request->input('customerid'));
        
        $worknumber= clearInput($request->input('worknumber'));
        $workdate= clearInput($request->input('workdate'));
        $workinvoice= clearInput($request->input('workinvoice'));
        
        $workconcept= clearInput($request->input('workconcept'));
        
        $workqtt= clearInput($request->input('workqtt'));
        $workprice= clearInput($request->input('workprice'));
        $workiva= clearInput($request->input('workiva'));
        $worktotal= clearInput($request->input('worktotal'));
        
        // comprobaciones idoneidad de datos recibidos
        
        // obtenemos el tipo de iva
        $idiva= IvaRates::where([
            ['idcompany',$idcompany],
            ['active',true],
            ['rate',$workiva]
        ])->first()->id;
        
        if (is_null($idiva) || $idiva===false || $idiva==0) {
            $messageWrong='Error en tipo de IVA';
            $error=true;
        }        
        
        // comprobamos la empresa
        if ($idcompany<1) {
            $messageWrong='Empresa inexistente';
            $error=true;            
        } else {
            // comprobamos usuario - empresa
            if ($idcompany != Auth::guard('')->user()->idcompany) {
                $messageWrong='Empresa no corresponde al usuario';
                $error=true;            
            }            
        }
        
        // comprobamos el cliente
        $cust= Customer::find($idcustomer);
        if (is_null($cust) || $cust===false ) {
            $messageWrong='Cliente inexistente';
            $error=true;
        } elseif ($cust->idcompany != $idcompany) {
            // comprobamos cliente - empresa
            $messageWrong='Cliente no pertenece a la empresa de facturación'.$cust->idcompany.'--'.$idcompany;
            $error=true;             
        }            
          
        // comprobamos el concepto
        if (strlen($workconcept)<5 || strlen($workconcept)>255) {
            $messageWrong='Longitud de concepto inadecuada (entre 5 y 255 caracteres)';
            $error=true;            
        } 

        if (!is_numeric($workqtt)) {
            $messageWrong='La cantidad del albarán debe ser un número';
            $error=true;             
        }

        if (!is_numeric($workprice)) {
            $messageWrong='El precio del albarán debe ser un número';
            $error=true;             
        }        
        
        if (!is_numeric($worktotal)) {
            $messageWrong='El importe total del albarán debe ser un número';
            $error=true;             
        }        


        // obtenemos los ivas activos
        $ivaRates= IvaRates::where([
            ['idcompany',$idcompany],
            ['active',true]
        ])->get();
        
        // obtenemos los clientes de la empresa
        $customers= Customer::where('idcompany',$idcompany)
                ->get();
        
        if ($error == false) {
            // no ha habido errores, grabamos
            $work= new Work;
            $work->work_date= converterDateToDDBB($workdate);
            $work->work_text=$workconcept;
            $work->work_qtt=$workqtt;
            $work->work_price=$workprice;
            $work->work_total=$worktotal;
            
            // le damos como número de serie estandar ALB
            $number=$this->getWorkNumber($idcompany, $workdate, 'ALB');
            $work->work_number=$number;
            
            $work->idcompany=$idcompany;
            $work->idcustomer=$idcustomer;
            $work->idiva=$idiva;
            $work->idinvoice=0;
            
            $work->save();
            
            $messageOK='Albarán grabado correctamente';
        
            return view('works/work')
            ->with('ivaRates',$ivaRates)
            ->with('customerSelected',$cust)                
            ->with('customers',$customers)
            ->with('work',$work)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);
            
        } else {
            
            return view('works/work')
            ->with('ivaRates',$ivaRates)
            ->with('customerSelected',$cust)                
            ->with('customers',$customers)
            ->with('work',$work)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);
        }

        
    }
    
    
    /**
     * Esta función muestra un formulario de opciones para buscar los albaranes 
     * @param type $idcompany
     * @return type
     */
    public function showWorksMenu($idcompany=0) {
        
                // mensajes
        $messageOK=$messageWrong=null;
        
        // obtenemos los clientes de la empresa
        $customers= Customer::where('idcompany',$idcompany)
                ->get();        
        
        $parameters=['cust'=>0,'state'=>0,'fechini'=>'','fechfin'=>'','amount'=>'','wknumber'=>''];
        
        return view('works/worksListBySelection')
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);        
        
    }
    
    
    public function searchWorksByOptions(Request $request) {
        
        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('idcustomer'));        
        $worknumber= clearInput($request->input('worknumber'));
        $fechini= clearInput($request->input('fechini'));
        $fechfin= clearInput($request->input('fechfin'));
        $amount= clearInput($request->input('amount'));
        $state= clearInput($request->input('state'));
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['cust'=>$idcustomer,'state'=>$state,'fechini'=> $fechini,
            'fechfin'=> $fechfin,'amount'=>$amount,'wknumber'=>$worknumber];        
        
        // FECHA INICIAL DEL LISTADO
        // si no se da, será el inicio del año en curso
        if (strlen($fechini)!=10) {
            $fechini='01-01-'.date('Y').' 00:00:00';
        } else {
            $fechini=$fechini.' 00:00:00';
        }
        $fechini= converterDateTimeToDDBB($fechini);

        // FECHA FINAL DEL LISTADO
        // si no se da, será el final del año en curso
        if (strlen($fechfin)!=10) {
            $fechfin='31-12-'.date('Y').' 23:59:59';
        } else {
            $fechfin=$fechfin.' 23:59:59';
        }
        $fechfin= converterDateTimeToDDBB($fechfin);
        
        // CANTIDAD DESDE
        // si no se da, será desde cero
        if (strlen($amount)<1) $amount=0;
        
        // Nº ALBARAN
        // si no se da, serán todos
        if (strlen($worknumber)<1) $alb='';
        else $alb=$worknumber;
        //die ($alb);
        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) $idcustomer='%%';
        
        // ESTADO
        // serán todos, los facturados o los no facturados
        if ($state==1) {
            // solamente no facturados
            $albstate='0';
            $sel='LIKE';
        } elseif ($state==2) {
            // solamente facturados
            // excluye lo que empiece por cero
            $albstate='0';
            $sel='NOT LIKE';
        } else {
            //todos los albaranes
            $albstate='%%';
            $sel='LIKE';            
        }
        
       // buscamos en DDBB
        $works=Work::where([
            ['works.idcustomer','LIKE',$idcustomer],
            ['works.idcompany',$idcompany],
            ['works.work_date','>=',$fechini],
            ['works.work_date','<',$fechfin],
            ['works.work_total','>=',$amount],
            ['works.work_number','LIKE','%'.$alb.'%'],
            ['works.idinvoice',$sel,$albstate]
            ])
                ->leftJoin('customers','customers.id','works.idcustomer')
                ->leftJoin('invoices','invoices.id','works.idinvoice')
                ->select('works.*','customers.customer_name as name','invoices.inv_number as invoicenumber')
                ->get();

        // obtenemos los clientes de la empresa para mostrar en select
        $customers= Customer::where('idcompany',$idcompany)
                ->get();
        
        
        return view('works/worksListBySelection')
            ->with('works',$works)
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);          
        
        
    }
    
    
    /**
     * Esta funcion obtiene el siguiente número de trabajo a facturar.
     * El formato del número que devuelve es el siguiente:
     * $serial(max 4 cifras) si lo tiene / año(4 cifras) - mes(2 cifras) - número consecutivo(relleno a 15) 
     * Ejemplo con serial un trabajo de octubre 2018: AB/201810000016
     * Ejemplo sin serial un trabajo de junio 2018: 201806000000023 
     * @param type $idcompany
     */
    private function getWorkNumber($idcompany,$thisdate,$serial='') {
        
        // el serial esta separado por barras. Hay que buscar
        // primero todos los números que correspondan con el año-mes
        // de thisdate
        $search=substr($thisdate,6,4).substr($thisdate,3,2);
        
        $works= Work::where([
            ['work_number','LIKE','%'.$search.'%']
        ])
            ->select('work_number')
            ->orderBy('work_number','DESC')->first();
        
        if (is_null($works)) {
            // no hay ningun albaran en ese mes
            
            if (strlen($serial)<1) {
                // no tenemos numero de serie
                $number=$search.'000000001';
            } else {
                $numprov=substr($serial,0,4).'/'.$search;
                for ($n= strlen($numprov);$n<14;$n++) {
                    // rellenamos con ceros hasta 14 long
                    $numprov.='0';
                }
                $number=$numprov.'1';
            }
        
            return $number;
        
            
        } else {
            // obtenemos el ultimo valor grabado
            $lastnum=$works->work_number;
            // comprobamos si es un numero
            $num=str_after($lastnum, $search);
            if (is_numeric($num)) {
                // siguiente numero
                $nextnum=$num+1;    
            } else {
                // por algun motivo no es numerico
                // es el primer numero
                $nextnum=1;
            }
            
            // componemos el numero en funcion de si tiene numero
            // de serie o no
            if (strlen($serial)<1) {
                // no tenemos numero de serie
                $chain='00000000'.$nextnum;
                $chain= substr($chain, -9);
                // search mide 6 y chain debe medir 9
                $number=$search.$chain;
            } else {
                // tenemos numero de serie de longitud variable,
                // por lo que llenamos chain de ceros y luego cortamos
                // al tamaño adecuado
                $numprov=substr($serial,0,4).'/'.$search;
                $chain='00000000'.$nextnum;
                $lengthNeeded=strlen($numprov)-15; // en negativo
                $chain= substr($chain, $lengthNeeded);
                                
                $number=$numprov.$chain;
            }
            
            return $number;
            
        }        
        
    }
    
}
