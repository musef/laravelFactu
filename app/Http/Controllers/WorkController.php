<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Mpdf\Mpdf;

use App\Work;
use App\Customer;
use App\IvaRates;
use App\Company;


/**
 * Esta clase controla los albaranes
 * 
 * SEGURIDAD: verifica la autenticación del usuario mediante middleware
 * Controla que el usuario pertenece a la misma empresa a realizar operaciones
 * Captura errores de DDBB y Exception
 * Verifica los datos recibidos por formulario, tanto en create como en update
 * En delete, borra por id + idcompany + idcustomer para evitar errores
 */

class WorkController extends Controller
{
    
    public function __construct() {
        $this->middleware('auth');
    }
    
    
    /**
     * Esta función muestra el formulario para grabar un nuevo albarán
     * 
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function worksMenu(Request $request) {
    
        // obtenemos el customerid, si se ha seleccionado en el select
        // esto no existe cuando entramos en el formulario
        if ($request->has('customerid')) {
            $customerid= clearInput($request->input('customerid'));
            ($customerid>0) ? $customer=Customer::find($customerid) : $customer=new Customer;
            //deshabilitado inicialmente la edicion
            ($customerid==0) ? $disabled='disabled' : $disabled='';
        } else {
          $customer=new Customer;  
          $disabled='disabled';
        }
         

        
        // generamos un objeto albarán en blanco
        $work=new Work();
        $work->work_typeiva=21;
        $work->work_qtt=1.00;
        $work->work_price=0.00;
        $work->work_date= date('d-m-Y');

        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        try {   
            // obtenemos los ivas activos
            $ivaRates= IvaRates::where([
                ['idcompany',$idcomp],
                ['active',true]
            ])->get();                       
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error obteniendo los tipos de iva activos';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error en base de datos obteniendo los tipos de iva activos - Error QW001';
        }

        try {   
            // obtenemos los clientes de la empresa
            $customers= Customer::where('idcompany',$idcomp)
                ->orderBy('customer_name')                    
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW002';
        }        
        
        return view('works/work')
            ->with('ivaRates',$ivaRates)
            ->with('customerSelected',$customer)   
            ->with('disabled',$disabled)                
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

        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // verificamos que el usuario pertenece a la empresa
        if ($idcompany == $idcomp) {
            
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
                
                // cambiamos para visualización
                $work->work_date= $workdate;

            }            
           
        } else {
            $messageWrong='Empresa no corresponde al usuario';  
            $cust= new Customer;
            // nuevo albaran
            $work=new Work();
            $work->work_typeiva=21;
            $work->work_qtt=1.00;
            $work->work_price=0.00;
            $work->work_date= date('d-m-Y');            
        }
        
        
            try {   
                // obtenemos los ivas activos
                $ivaRates= IvaRates::where([
                    ['idcompany',$idcomp],
                    ['active',true]
                ])->get();                       
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $ivaRates=null;
                $messageWrong='Error obteniendo los tipos de iva activos';
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $ivaRates=null;
                $messageWrong='Error en base de datos obteniendo los tipos de iva activos - Error QW003';
            }

            try {   
                // obtenemos los clientes de la empresa
                $customers= Customer::where('idcompany',$idcomp)
                    ->orderBy('customer_name')                        
                    ->get();                      
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error obteniendo la lista de los clientes de la empresa';
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW004';
            } 
        
            return view('works/work')
            ->with('ivaRates',$ivaRates)
            ->with('customerSelected',$cust)                
            ->with('customers',$customers)
            ->with('work',$work)
            ->with('disabled','')                     
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);        
    }
    
    
    /**
     * Esta función muestra un formulario de opciones para buscar los albaranes 
     * @param type $idcompany
     * @return type
     */
    public function showWorksMenu() {

        // mensajes
        $messageOK=$messageWrong=null;
        
        // verificamos que el usuario pertenece a la empresa
        $idcomp= Auth::guard('')->user()->idcompany;
        
        try {   
            // obtenemos los clientes de la empresa
            $customers= Customer::where('idcompany',$idcomp)
                ->orderBy('customer_name')                    
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW005';
        }                                
        
        $parameters=['cust'=>0,'state'=>0,'fechini'=>'','fechfin'=>'','amount'=>'','wknumber'=>''];
        
        return view('works/worksListBySelection')
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)
            ->with('totalList',0)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);        
        
    }
    
    
    
    /**
     * Esta función muestra uno o varios trabajos en forma de listado, en función
     * de la selección efectuada en el formulario.
     * Los filtros del formulario son no excluyentes, y trabajan en combinación
     * @param Request $request
     * @return type
     */
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
        
        // total del listado
        $totalList=0;        
        
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
        
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // verificamos que el usuario pertenece a la empresa
        if ($idcompany == $idcomp) {

            try {

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
                     ->orderBy('works.work_number')
                     ->get();
                
                // sumatorio
                $totalList=Work::where([
                 ['works.idcustomer','LIKE',$idcustomer],
                 ['works.idcompany',$idcompany],
                 ['works.work_date','>=',$fechini],
                 ['works.work_date','<',$fechfin],
                 ['works.work_total','>=',$amount],
                 ['works.work_number','LIKE','%'.$alb.'%'],
                 ['works.idinvoice',$sel,$albstate]
                 ])
                     ->select('works.work_total')
                     ->sum('works.work_total');                

            } catch (Exception $ex) {

                // generamos un objeto en blanco
                $works=null;
                $customers=null;
                $messageWrong='Error en base de datos obteniendo albaranes';

            } catch (QueryException $quex) {

                // generamos un objeto en blanco
                $works=null;
                $customers=null;            
                $messageWrong='Error en base de datos obteniendo albaranes - Error QW006';

            }     
            
            try {   
                // obtenemos los clientes de la empresa
                $customers= Customer::where('idcompany',$idcomp)
                    ->orderBy('customer_name')                        
                    ->get();                      
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error obteniendo la lista de los clientes de la empresa';
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW007';
            }            
            
        } else {
            $messageWrong='Empresa no corresponde al usuario';
                // generamos un objeto en blanco
                $works=null;
                $customers=null;
        }   
                        
        return view('works/worksListBySelection')
            ->with('works',$works)
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)
            ->with('totalList',$totalList)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);          
                
    }
    
    
    /**
     * Esta función obtiene la selección para el listado de albaranes, a partir
     * del formulario correspondiente, y muestra el listado en  formato pdf 
     * en la misma pantalla de selección.
     * 
     * @param Request $request
     * @return type
     */
    public function worksPdfList(Request $request) {
        
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
        
        // total del listado
        $totalList=0;    
        
        
        // FECHA INICIAL DEL LISTADO
        // si no se da, será el inicio del año en curso
        if (strlen($fechini)!=10) {
            $fechini='01-01-'.date('Y').' 00:00:00';
        } else {
            $fechini=$fechini.' 00:00:00';
        }
        // texto del listado
        $textlist='Desde '.substr($fechini,0,10);        
        $fechini= converterDateTimeToDDBB($fechini);

        // FECHA FINAL DEL LISTADO
        // si no se da, será el final del año en curso
        if (strlen($fechfin)!=10) {
            $fechfin='31-12-'.date('Y').' 23:59:59';
        } else {
            $fechfin=$fechfin.' 23:59:59';
        }
        // texto del listado
        $textlist.=' hasta '.substr($fechfin,0,10);        
        $fechfin= converterDateTimeToDDBB($fechfin);
        
        // CANTIDAD DESDE
        // si no se da, será desde cero
        if (strlen($amount)<1) {
            $amount=0;
        } else {
            $textlist.=' - Importes desde '.$amount.' euros';
        }
        
        // Nº ALBARAN
        // si no se da, serán todos
        if (strlen($worknumber)<1) {
            $alb='';
            $textlist.=' - Todos los albaranes';
        } else {
            $alb=$worknumber;
            $textlist.=' - Solo el albarán seleccionado';            
        }
        
        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) {
            $idcustomer='%%';
            $textlist.=' - Todos los clientes';
        } else {
            $textlist.=' - del cliente seleccionado';
        }
        
        // ESTADO
        // serán todos, los facturados o los no facturados
        if ($state==1) {
            // solamente no facturados
            $albstate='0';
            $sel='LIKE';
            $textlist.=' - Albaranes sin facturar';            
        } elseif ($state==2) {
            // solamente facturados
            // excluye lo que empiece por cero
            $albstate='0';
            $sel='NOT LIKE';
            $textlist.=' - Albaranes facturados';            
        } else {
            //todos los albaranes
            $albstate='%%';
            $sel='LIKE'; 
            $textlist.=' - Facturados y sin facturar';
        }        
        
        
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // verificamos que el usuario pertenece a la empresa
        if ($idcompany == $idcomp) {

            try {

                // buscamos en DDBB
                // listado por numero de albaran
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
                     ->orderBy('works.work_number')
                     ->get();
                
                // buscamos en DDBB
                $totalList=Work::where([
                 ['works.idcustomer','LIKE',$idcustomer],
                 ['works.idcompany',$idcompany],
                 ['works.work_date','>=',$fechini],
                 ['works.work_date','<',$fechfin],
                 ['works.work_total','>=',$amount],
                 ['works.work_number','LIKE','%'.$alb.'%'],
                 ['works.idinvoice',$sel,$albstate]
                 ])
                     ->select('works.work_total')
                     ->sum('works.work_total');                 
                
            } catch (Exception $ex) {

                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;
                $messageWrong='Error en base de datos obteniendo albaranes';
                // regreso al formulario
                $parameters=['cust'=>0,'state'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>0,'wknumber'=>''];             
                return view('works/worksListBySelection')
                    ->with('customersSel',$customers)
                    ->with('parameters',$parameters)
                    ->with('totalList',0)                
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);                

            } catch (QueryException $quex) {

                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;            
                $messageWrong='Error en base de datos obteniendo albaranes - Error QW008';
                // regreso al formulario
                $parameters=['cust'=>0,'state'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>0,'wknumber'=>''];             
                return view('works/worksListBySelection')
                    ->with('customersSel',$customers)
                    ->with('parameters',$parameters)
                    ->with('totalList',0)                
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);                

            }     
                      
            
        } else {
            $messageWrong='Empresa no corresponde al usuario';
                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;
        }   
                        
        // cabecera de la factura
        $data='
            <head>
                <title>Listado albaranes</title>
                <meta charset="utf-8">
                <style>
                html {
                  min-height: 100%;
                  position: relative;
                }
                table {
                    font-size:8px;
                }
                th {                   
                    border:1px solid black;
                    margin: 0px 0px 0px 0px;
                    min-width:12%;
                }
                td {
                    padding-left:5px;
                    padding-right:5px;
                }
                .right {
                    text-align:right;
                }
                </style>
            </head>
            <body style="width:1000px;">
            
                <div style="width:100%; margin: 0px 5px 5px 0px;border:1px solid black">

                <h2>Listado de albaranes</h2>
                <p>'.$textlist.'</p>
                </div>';
        
        // cuerpo de la factura
        $data.='
            <div style="min-height:500px;border:1px solid black" >
                <table>
                    <tr>
                        <th >Nº albarán</th>
                        <th >Cliente</th>
                        <th >Fecha</th>
                        <th >Importe</th>
                        <th >Concepto</th>
                    </tr>                
';
        $count=0;
        if (isset($works) && count($works)>0) {
            foreach ($works as $work) {
                $data.='
                    <tr>                       
                       <td> <span style="width:10%">'.substr($work->work_number,0,15).'</span></td>
                       <td> <span style="width:30%">'.substr($work->name,0,25).'</span></td>
                       <td> <span style="width:10%">'.converterDate($work->work_date).'</span></td>
                       <td class="right"> <span style="width:10%;text-align:right;">'.number_format($work->work_total,2,',','.').' </span></td>
                       <td> <span style="width:40%">'.substr($work->work_text,0,75).'</span></td>
                    </tr>';
                $count++;
                // paginamos
                
                if ($count>=45) {
                    // pie de la factura
                    $count=0;
                    $data.='</table></div><br /><br />

                    <div style="width:100%; margin: 0px 5px 5px 0px;border:1px solid black">
                    <h2>Listado de albaranes</h2>
                    <p>'.$textlist.'</p>
                    </div>
                    <div style="min-height:500px;border:1px solid black" >
                        <table>
                            <tr>
                                <th>Nº albarán</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Importe</th>
                                <th>Concepto</th>
                            </tr>';                
                }

            }            
        } else {
            $data.='
                </table>
                
                <div style="width:100%;" >                        
                   <input type="text" value="No se ha obtenido ningún dato" style="width:100%;height:50px;border:none;" >
                </div>';            
        }

        $data.='</table></div>';
        
        
        $data.='
            <br />
            <hr>
            <br />
            <div style="width:100%;font-weight:bold;font-size:1.4em;" >               
                <input type="text" value="Total importes ...: '. number_format($totalList,2,',','.').' euros" 
                    style="width:50%;height:50px;text-align:center;border:none" >
            </div>';
        
        // generamos un pdf en vista directa sobre la pantalla actual
        $this->showPdf($data);
        
        return;
        
    }    
    
    
    
    /**
     * Esta función recupera para edición el albarán seleccionado en el listado
     * @param type $id
     * @return type
     */
    public function editWork($id=0) {
        
        // mensajes
        $messageOK=$messageWrong=null;
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;   
        
        //deshabilitado inicialmente la edicion
        ($id==0) ? $disabled='disabled' : $disabled='';

            try {
                // obtenemos el trabajo seleccionado
                // buscamos en DDBB
                 $work=Work::where([
                     ['works.id',$id],
                     ['works.idcompany',$idcomp],
                     ])
                    ->leftJoin('invoices','invoices.id','works.idinvoice')
                    ->select('works.*','invoices.inv_number as invoicenumber')
                    ->first();            

                // verificamos que el usuario pertenece a la empresa
                if ($work->idcompany == $idcomp) {     
                    
                    $work->work_date=converterDate($work->work_date);

                    // habilitamos o desabilitamos la edición en función de si ya está facturado                    
                    (strlen($work->invoicenumber)>2) ? $disabled='disabled' : $disabled='';
                    
                    // recuperamos el cliente
                    $customerid=$work->idcustomer;        
                    ($customerid>0) ? $customer=Customer::find($customerid) : $customer=new Customer;                         
                    
                    $messageOK='Recuperado el albarán seleccionado';                    
                    
                } else {
                    $messageWrong='Empresa no corresponde al usuario';
                    // generamos un objeto albarán en blanco
                    $work=new Work();
                    $work->work_typeiva=21;
                    $work->work_qtt=1.00;
                    $work->work_price=0.00;
                    $work->work_date= date('d-m-Y');

                    //generamos un cliente en blanco
                    $customer=new Customer;
                }                        
                 

            } catch (Exception $ex) {

                // generamos un objeto albarán en blanco
                $work=new Work();
                $work->work_typeiva=21;
                $work->work_qtt=1.00;
                $work->work_price=0.00;
                $work->work_date= date('d-m-Y');

                //generamos un cliente en blanco
                $customer=new Customer;

                $messageWrong='Error obteniendo el albarán';

            } catch (QueryException $quex) {

                // generamos un objeto albarán en blanco
                $work=new Work();
                $work->work_typeiva=21;
                $work->work_qtt=1.00;
                $work->work_price=0.00;
                $work->work_date= date('d-m-Y');

                //generamos un cliente en blanco
                $customer=new Customer;

                $messageWrong='Error en base de datos obteniendo el albarán - Error QW009';

            }            
             
        
        try {   
            // obtenemos los ivas activos
            $ivaRates= IvaRates::where([
                ['idcompany',$idcomp],
                ['active',true]
            ])->get();                       
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error obteniendo los tipos de iva activos';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error en base de datos obteniendo los tipos de iva activos - Error QW010';
        }
        
        try {   
            // obtenemos los clientes de la empresa
            $customers= Customer::where('idcompany',$idcomp)
                ->orderBy('customer_name')                    
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW011';
        }          
        
        
        return view('works/work')
            ->with('ivaRates',$ivaRates)
            ->with('customerSelected',$customer)                
            ->with('customers',$customers)
            ->with('disabled',$disabled)
            ->with('work',$work)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);        
        
    }

    
    /**
     * Esta función modifica un albarán, conforme a los datos enviados por
     * formulario.
     * Estos datos son previamente verificados y comprobados.
     * 
     * @param Request $request
     * @return type
     */
    public function updateWork( Request $request) {
        
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
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        try {
            
            // verificamos la correspondiencia usuario - empresa
            if ($idcompany == $idcomp) {
             // usuario correcto: puede operar con la empresa           

             // comprueba si esta facturado o no
             // si esta facturado, no se puede modificar
                if ($workinvoice==0) {
                    // no esta facturado

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

                    if ($error == false) {

                        // obtenemos el albaran para modificar
                        $work= Work::find($idwork);

                        if (!is_null($work) && $work!=false) {
                            
                            // no ha habido errores, grabamos
                            $work->work_date= converterDateToDDBB($workdate);
                            $work->work_number=$worknumber;
                            $work->work_text=$workconcept;
                            $work->work_qtt=$workqtt;
                            $work->work_price=$workprice;
                            $work->work_total=$worktotal;

                            $work->idcompany=$idcompany;
                            $work->idcustomer=$idcustomer;
                            $work->idiva=$idiva;                    

                            $work->save();

                            // mensaje ok y 
                            $messageOK='Albarán modificado correctamente';                        

                            // para mostrar en pantalla !!
                            $work->work_date=converterDate($work->work_date);                            
                            
                        } else {
                            // albaran no existe
                            $messageWrong='No ha sido posible localizar el albarán en la base de datos';
                        }
                    }
                } else {
                    // esta facturado, no se puede modificar
                    $messageWrong='NO es posible modificar un albarán facturado';    
                }

            } else {
                 // el usuario no corresponde a la empresa 
                $messageWrong='Empresa no corresponde al usuario';            
            }            
            
        } catch (Exception $ex) {
            $messageWrong='Error en base de datos: imposible eliminar el albarán';
            $customer=new Customer;
            // generamos un objeto albarán en blanco
            $work=new Work();
            $work->work_typeiva=21;
            $work->work_qtt=1.00;
            $work->work_price=0.00;
            $work->work_date= date('d-m-Y');            
        } catch (QueryException $quex) {
            $messageWrong='Error en base de datos: imposible eliminar el albarán - Error QW012';
            $customer=new Customer;
            // generamos un objeto albarán en blanco
            $work=new Work();
            $work->work_typeiva=21;
            $work->work_qtt=1.00;
            $work->work_price=0.00;
            $work->work_date= date('d-m-Y');            
        }
        
        try {   
            // obtenemos los ivas activos
            $ivaRates= IvaRates::where([
                ['idcompany',$idcomp],
                ['active',true]
            ])->get();                       
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error obteniendo los tipos de iva activos';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error en base de datos obteniendo los tipos de iva activos - Error QW013';
        }
        
        try {   
            // obtenemos los clientes de la empresa
            $customers= Customer::where('idcompany',$idcomp)
                ->orderBy('customer_name')                    
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW014';
        }  
  
        return view('works/work')
        ->with('ivaRates',$ivaRates)
        ->with('customerSelected',$cust)                
        ->with('customers',$customers)
        ->with('work',$work)
        ->with('disabled','')                
        ->with('messageOK',$messageOK)
        ->with('messageWrong',$messageWrong);
       
    }    
    
    
    /**
     * Esta función realiza el borrado de un albarán editado en el formulario
     * 
     * @param Request $request
     * @return type
     */
    public function deleteWork(Request $request) {
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // datos del formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('customerid'));        
        $workid= clearInput($request->input('workid'));        
        
        // mensajes
        $messageOK=$messageWrong=null;        
        
        try {
            
            // verificamos pertenencia del usuario
            if ($idcomp==$idcompany) {

                // borramos el albarán, y nos aseguramos que pertenezca
                // a la empresa y al cliente correspondiente
                $res= Work::where([
                    ['works.id',$workid],
                    ['works.idcustomer',$idcustomer],
                    ['works.idcompany',$idcompany]                
                ])->delete();

                $messageOK='Albarán borrado correctamente';

                // generamos un objeto albarán en blanco
                $work=new Work();
                $work->work_typeiva=21;
                $work->work_qtt=1.00;
                $work->work_price=0.00;
                $work->work_date= date('d-m-Y');      

                //generamos cliente en blanco
                $customer=new Customer;            

            } else {

                $messageWrong='El usuario no puede realizar la opción de borrado, no pertenece a la empresa';

                // obtenemos el trabajo seleccionado
                // buscamos en DDBB
                 $work=Work::where([
                     ['works.id',$workid],
                     ['works.idcompany',$idcompany],
                     ])
                    ->leftJoin('invoices','invoices.id','works.idinvoice')
                    ->select('works.*','invoices.inv_number as invoicenumber')
                    ->first();            

                 // para mostrar en pantalla !!
                $work->work_date=converterDate($work->work_date);

                // recuperamos el cliente
                $customerid=$work->idcustomer;        
                ($customerid>0) ? $customer=Customer::find($customerid) : $customer=new Customer;                          

            }            
            
        } catch (Exception $ex) {
            $messageWrong='Error en base de datos: imposible eliminar el albarán';
            $customer=new Customer;
            // generamos un objeto albarán en blanco
            $work=new Work();
            $work->work_typeiva=21;
            $work->work_qtt=1.00;
            $work->work_price=0.00;
            $work->work_date= date('d-m-Y');            
        } catch (QueryException $quex) {
            $messageWrong='Error en base de datos: imposible eliminar el albarán - Error QW015';
            $customer=new Customer;
            // generamos un objeto albarán en blanco
            $work=new Work();
            $work->work_typeiva=21;
            $work->work_qtt=1.00;
            $work->work_price=0.00;
            $work->work_date= date('d-m-Y');            
        }
          
        try {   
            // obtenemos los ivas activos
            $ivaRates= IvaRates::where([
                ['idcompany',$idcomp],
                ['active',true]
            ])->get();                       
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error obteniendo los tipos de iva activos';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $ivaRates=null;
            $messageWrong='Error en base de datos obteniendo los tipos de iva activos - Error QW016';
        }
        
        try {   
            // obtenemos los clientes de la empresa
            $customers= Customer::where('idcompany',$idcomp)
                ->orderBy('customer_name')                    
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW017';
        }            
        
        return view('works/work')
            ->with('ivaRates',$ivaRates)
            ->with('customerSelected',$customer)                
            ->with('customers',$customers)
            ->with('work',$work)
            ->with('disabled','')                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);
        
    }
    
    
    /**
     * Esta función realiza el borrado de un albarán editado en el formulario
     * 
     * @param Request $request
     * @return type
     */
    public function deleteWorkFromList(Request $request, $idwork=0) {
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // datos del formulario
        $idcompany= clearInput($request->input('companyid'));               
        
        // mensajes
        $messageOK=$messageWrong=null;
        
        // mostrarlos de regreso al formulario
        $parameters=['cust'=>0,'state'=>0,'fechini'=> '',
            'fechfin'=> '','amount'=>0,'wknumber'=>''];         
        
        try {
            
            // verificamos pertenencia del usuario
            if ($idcomp==$idcompany) {

                // borramos el albarán, y nos aseguramos que pertenezca
                // a la empresa correspondiente
                $res= Work::where([
                    ['works.id',$idwork],
                    ['works.idcompany',$idcompany]                
                ])->delete();

                $messageOK='Albarán borrado correctamente';           

            } else {

                $messageWrong='El usuario no puede realizar la opción de borrado, no pertenece a la empresa';                         

            }            
            
        } catch (Exception $ex) {
            $messageWrong='Error en base de datos: imposible eliminar el albarán';
           
        } catch (QueryException $quex) {
            $messageWrong='Error en base de datos: imposible eliminar el albarán - Error QW018';           
        }
          

        
        try {   
            // obtenemos los clientes de la empresa
            $customers= Customer::where('idcompany',$idcomp)
                ->orderBy('customer_name')                    
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW019';
        }            
        
        
        return view('works/worksListBySelection')
            ->with('works',null)
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);         
        
    }    
    
    
    /**
     * Esta función genera una vista por pantalla de un documento PDF, en la misma
     * pantalla (_self) donde se estaba consultando la factura
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function showPdfWork(Request $request,$id=0) {
        
        // mensajes
        $messageOK=$messageWrong=null;
      
        // tomamos el id de la factura por formulario, si es que estamos en edición
        // individual de la factura
        if ($request->has('workid') && clearInput($request->input('workid'))>0) 
            $id=clearInput($request->input('workid'));
        
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        $work=new Work;

       
                // edicion
                try {
                    // obtenemos los datos de la empresa
                    $company=Company::find($idcomp);

                        // obtenemos el albarán correspondiente al id
                        $work= Work::where([
                            ['id',$id],
                            ['idcompany',$idcomp]
                        ])->first();  

                    // obtenemos el tipo de iva del albarán
                    $rate= IvaRates::where([
                        ['idcompany',$idcomp],
                        ['id',$work->idiva]
                    ])->first()->rate;

                    // obtenemos el cliente del albarán
                    $customer= Customer::find($work->idcustomer);


                } catch (Exception $ex) {
                    // generamos un objeto en blanco
                    $customers=null;
                    $messageWrong='Error obteniendo la lista de los clientes de la empresa';
                    // regreso al formulario
                    $parameters=['cust'=>0,'state'=>0,'fechini'=> '',
                        'fechfin'=> '','amount'=>0,'wknumber'=>''];             
                    return view('works/worksListBySelection')
                        ->with('customersSel',$customers)
                        ->with('parameters',$parameters)
                        ->with('totalList',0)                
                        ->with('messageOK',null)
                        ->with('messageWrong',$messageWrong);                    
                } catch (QueryException $quex) {
                    // generamos un objeto en blanco
                    $customers=null;
                    $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QW020';
                    // regreso al formulario
                    $parameters=['cust'=>0,'state'=>0,'fechini'=> '',
                        'fechfin'=> '','amount'=>0,'wknumber'=>''];             
                    return view('works/worksListBySelection')
                        ->with('customersSel',$customers)
                        ->with('parameters',$parameters)
                        ->with('totalList',0)                
                        ->with('messageOK',null)
                        ->with('messageWrong',$messageWrong);                    
                }           
               
        // cabecera del albarán
        $data='
            <head>
                <title>Mostrar albarán</title>
                <meta charset="utf-8">
                <style>
                html {
                  min-height: 100%;
                  position: relative;
                }
                h2 {
                    font-size:16px;
                    margin-bottom:0px;
                    margin-top:0px;
                }
                label {
                    font-size:14px;
                    margin-bottom:0px;
                    margin-top:0px;
                }                
                table {
                    font-size:16px;
                }
                th {                   
                    border:1px solid black;
                    margin: 0px 0px 0px 0px;
                }
                td {
                    padding-left:5px;
                    padding-right:5px;
                }
                .fieldlong {
                    text-align:center;
                    width:40%;
                }
                .fieldshort {
                    width:10%;
                }
                .linesum {
                    font-size:0.8em;
                    font-weight:bold;
                }
                

                </style>                
            </head>
            <body style="width:1000px;">
              <div style="width:100%;border:1px solid black">
                <div style="width:99%; margin: 5px 5px 5px 5px;">

                    <div style="width:100%;border:1px solid black" >

                        <div style="width:50%"> 
                            <h2>'.$company->company_name.'</h2>
                            <h2>'.$company->company_address.'</h2>
                            <h2>'.$company->company_zip.' - '.$company->company_city.'</h2>
                            <h2>'.$company->company_nif.'</h2>
                        </div>

                        <div style="margin-left:70%;"> 
                            <label> Cliente:</label> <br/>
                            <label>'.$customer->customer_name.'</label><br />
                            <label>'.$customer->customer_address.'</label><br />
                            <label>'.$customer->customer_zip.' - '.$customer->customer_city.'</label><br />
                            <label>'.$customer->customer_nif.'</label><br />
                        </div>

                        <hr>
                        <br />

                        <div style="width:95%; margin: 5px 5px 5px 5px;font-size:1em;font-weight:bold">
                            <label>Albarán '.$work->work_number.'</label>
                            <label style="margin-left:75%">Fecha Albarán '.converterDate($work->work_date).'</label>
                        </div>

                    </div>';
        
        // cuerpo del albarán
        $data.='
            <div style="min-height:200px;" >
                <table>
                    <tr>
                        <th>Código</th>
                        <th class="fieldshort">Uds</th>
                        <th class="fieldlong">Concepto</th>
                        <th class="fieldshort">% Iva</th>
                        <th class="fieldshort">Precio</th>
                        <th>Importe</th>                        
                    </tr>                

                    <tr>                       
                       <td> <span> -- </span></td>
                       <td> <span>'.number_format($work->work_qtt,2,',','.').'</span></td>
                       <td> <span>'.substr($work->work_text,0,42).'</span></td>
                       <td> <span>'.number_format($rate,2,',','.').' </span></td>
                       <td> <span>'.number_format($work->work_price,2,',','.').'</span></td>
                       <td> <span>'.number_format(($work->work_qtt*$work->work_price),2,',','.').'</span></td>
                    </tr>';           
        
        // si el concepto excede de 51 chars., hacemos varias líneas
        if (strlen($work->work_text) > 42) {
            $conceptlength= strlen($work->work_text);
            for ($n=42;$n<$conceptlength;$n=$n+42) {
                // paginamos líneas de 51 caracteres de longitud
                $data.='                        
                    <tr>                       
                       <td> <span> -- </span></td>
                       <td> <span> </span></td>
                       <td> <span>'.substr($work->work_text,$n,42).'</span></td>
                       <td> <span> </span></td>
                       <td> <span> </span></td>
                       <td> <span> </span></td>
                    </tr>';
            }            
        }
      
        $data.='</table></div>';        
        
        // resumen importes de albarán       
        $bimp=$work->work_qtt*$work->work_price;
        $cuota=($work->work_qtt*$work->work_price)*$rate/100;
        
        $data.='
            <br />
            <hr>
                <table>
                    <tr>
                       <td class="linesum">Base Imponible</td>
                       <td class="linesum">'.number_format($bimp,2,',','.').' </td>
                       <td class="linesum"> | Cuota IVA</td>
                       <td class="linesum"> '.number_format($cuota,2,',','.').' </td>
                       <td class="linesum"> | Total Albarán</span></td>
                       <td class="linesum"> '. number_format($work->work_total,2,',','.').' €</td>
                    </tr>
                </table>';                     
     
        // pie 
        $data.='</div></div>
            </body>';
        
        // generamos un pdf en vista directa sobre la pantalla actual
        $this->showPdf($data,false);
        return;
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
            ['work_number','LIKE','%'.$search.'%'],
            ['idcompany',$idcompany]
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
    
    
    /**
     * Esta función fabrica un documento pdf suministrándole un html.
     * El formato será un documento utf-8 en formato A4 portrait
     * @param type $data
     * @return type
     */
    private function showPdf($data, $generated=true) {
        
        // creamos el directorio temporal
        $mpdf = new Mpdf([
            'tempDir' => __DIR__ . '/tmp',
            'mode' => 'utf-8', 
            'format' => [190, 236],
            'orientation' => 'P']);
        
        // generamos la fecha de emisión si true
        if ($generated===true) $mpdf->SetHeader('Emitido el '.date('d-m-Y H:i:s',time()));
                

        // generamos el html
        $mpdf->WriteHTML($data);

        // Output a PDF file directly to the browser
        $mpdf->Output();
        
        return;            
            
    }
    
}
