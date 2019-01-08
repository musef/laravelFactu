<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Mpdf\Mpdf;

use App\Customer;
use App\Work;
use App\Invoice;
use App\PaymentMethod;
use App\IvaRates;
use App\Company;
use App\Config;




class InvoiceController extends Controller
{
    
    public function __construct() {
        $this->middleware('auth');
    }
    
    
    
    /**
     * Esta función muestra el menú de facturación de albaranes
     * @return type
     */
    public function invoicesMenu() {
                
        // mensajes
        $messageOK=$messageWrong=null;       
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['invdate'=> converterDate(now()) ,'cust'=>0,'fechini'=> '','fechfin'=> '','format'=>0,'wknumber'=>''];        
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
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
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI001';
        }          
        
        return view('invoices/invoicesMenu')
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);  
        
    }

    
    /**
     * Esta función muestra el listado de los albaranes pendientes de facturar, 
     * en función de los parámetros suministrados en el formulario
     * @param Request $request
     * @return type
     */
    public function showWorksList(Request $request) {

        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('idcustomer'));        
        $worknumber= clearInput($request->input('worknumber'));
        $fechini= clearInput($request->input('fechini'));
        $fechfin= clearInput($request->input('fechfin'));
        $format= clearInput($request->input('format'));
        $invdate= clearInput($request->input('invdate'));
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['invdate'=> $invdate,'cust'=>$idcustomer,'fechini'=> $fechini,
            'fechfin'=> $fechfin,'format'=>$format,'wknumber'=>$worknumber];        
        
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
        
        // FECHA FINAL DE FACTURACION
        // si no es de 10, se toma la fecha actual
        if (strlen($invdate)!=10) {
            $invdate=converterDate(now());
        }                 
        // verificamos muy por encima la fecha
        if ( !(substr($invdate,0,2) > 0 && substr($invdate,0,2) < 32 &&
                substr($invdate,3,2) > 0 && substr($invdate,3,2) < 13 &&
                substr($invdate,6,4) > 2015 && substr($invdate,6,4) < 2050)) {
            // si no cumple parametros, facturamos como hoy
            $invdate=converterDate(now());
        }
   
        // Nº ALBARAN
        // si no se da, serán todos
        if (strlen($worknumber)<1) $alb='';
        else $alb=$worknumber;

        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) $idcustomer='%%';
        
        
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
                 ['works.work_number','LIKE','%'.$alb.'%'],
                 ['works.idinvoice',0]
                 ])
                     ->leftJoin('customers','customers.id','works.idcustomer')
                     ->leftJoin('invoices','invoices.id','works.idinvoice')
                     ->select('works.*','customers.customer_name as name','invoices.inv_number as invoicenumber')
                     ->orderBy('works.work_number')
                     ->get();         

            } catch (Exception $ex) {

                // generamos un objeto en blanco
                $works=null;
                $customers=null;
                $messageWrong='Error en base de datos obteniendo albaranes';

            } catch (QueryException $quex) {

                // generamos un objeto en blanco
                $works=null;
                $customers=null;            
                $messageWrong='Error en base de datos obteniendo albaranes - Error QI002';

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
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI003';
            }            
            
        } else {
            $messageWrong='Empresa no corresponde al usuario';
                // generamos un objeto en blanco
                $works=null;
                $customers=null;
        }   
                        
        return view('invoices/invoicesMenu')
            ->with('works',$works)
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);         
        
    }
    
    

    /**
     * Esta función crea una nueva factura, a partir de los datos recolectados por
     * formulario.
     * Esta creación es automática, ya que toma todas las facturas obtenidas por el
     * listado, y las crea con la fecha introducida en el formulario.
     * 
     * PUede crear una factura por albarán o factura para todos los albaranes de
     * cada cliente.
     * 
     * Modifica en el albarán el campo idinvoice para marcarlo como facturado
     * 
     * @param Request $request
     * @return type
     */
    public function createInvoices(Request $request) {

        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('idcustomer'));        
        $worknumber= clearInput($request->input('worknumber'));
        $fechini= clearInput($request->input('fechini'));
        $fechfin= clearInput($request->input('fechfin'));
        $format= clearInput($request->input('format'));
        $invdate= clearInput($request->input('invdate'));        
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['invdate'=> $invdate,'cust'=>$idcustomer,'fechini'=> $fechini,
            'fechfin'=> $fechfin,'format'=>$format,'wknumber'=>$worknumber];        
        
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
        
        // FECHA FINAL DE FACTURACION
        // si no es de 10, se toma la fecha actual
        if (strlen($invdate)!=10) {
            $invdate=converterDate(now());
        }                 
        // verificamos muy por encima la fecha
        if ( !(substr($invdate,0,2) > 0 && substr($invdate,0,2) < 32 &&
                substr($invdate,3,2) > 0 && substr($invdate,3,2) < 13 &&
                substr($invdate,6,4) > 2015 && substr($invdate,6,4) < 2050)) {
            // si no cumple parametros, facturamos como hoy
            $invdate=converterDate(now());
        }        
        
        
        // Nº ALBARAN
        // si no se da, serán todos
        if (strlen($worknumber)<1) $alb='';
        else $alb=$worknumber;
        
        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) $idcustomer='%%';
        
        // formato generación facturas
        ($format==0) ? $format=0 : $format=1;
        
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
                 ['works.work_number','LIKE','%'.$alb.'%'],
                 ['works.idinvoice',0]
                 ])
                    ->orderBy('works.idcustomer','ASC')
                    ->orderBy('works.work_number','ASC')                        
                    ->get();         

            } catch (Exception $ex) {

                // generamos un objeto en blanco
                $works=null;
                $customers=null;
                $messageWrong='Error en base de datos obteniendo albaranes';

            } catch (QueryException $quex) {

                // generamos un objeto en blanco
                $works=null;
                $customers=null;            
                $messageWrong='Error en base de datos obteniendo albaranes - Error QI004';

            }
            
            // formato de elaboración de facturas
            if ($format==0) {
                // todos los albaranes de un cliente en una única factura
                
                $errors=false;
                
                if (!(is_null($works) || count($works)<1)) {

                    //reseteamos el cliente
                    $idcust=0;
                    // reseteamos numero de factura
                    $number=0;
                    // creamos la lista de albaranes de cada factura
                    $worklist=array();
                    // reseteamos a cero bases y cuotas
                    $b0=$c0=$b1=$c1=$b2=$c2=$b3=$c3=0;
                    // reseteamos a uno tipo
                    $i0=$i1=$i2=$i3=1;                

                    // el procedimiento es el siguiente:
                    // comparamos el cliente del albaran anterior con el del nuevo leido
                    // - si son el mismo, acumulamos los importes y guardamos en el array el
                    // id del albaran para actualizar al grabar la factura
                    // - si son distintos, es que es el primero o que es otro cliente
                    // por lo tanto, si es nuevo simplemente acumulamos
                    // pero si es nuevo, pues grabamos los datos del cliente anterior
                    // y acumulamos los datos del nuevo, reseteando las variables pertinentes

                    foreach ($works as $work) {

                        // tomamos el cliente del albaran
                        $idcu=$work->idcustomer;

                        // y lo comparamos con el cliente anterior
                        // para saber si tenemos que facturar o seguir acumulando
                        if ($idcust==$idcu || $number==0) {

                            // aqui se llega cuando hay dos o mas albaranes porque es
                            // acumulación o cuando es el primer albaran
                           if ($number==0) {
                               // es el primer albaran por lo que acumulamos
                                $number=$this->generateNextInvoiceNumber($idcompany, $invdate);
                                // y actualizamos el cliente con el cliente anterior
                                $idcust=$idcu;
                           }
                            $idco=$work->idcompany;
                            $iva=$this->getIvaType($work->idiva);
                            $ivatype=$iva[0];
                            $ivarate=$iva[1];

                            if ($ivatype==3) {
                                $b3+=(($work->work_qtt) * ($work->work_price));
                                $i3=$work->idiva;
                                $c3=$b3*$ivarate/100;
                            } elseif ($ivatype==2) {
                                $b2+=(($work->work_qtt) * ($work->work_price));
                                $i2=$work->idiva;
                                $c2=$b2*$ivarate/100;
                            } elseif ($ivatype==1) {
                                $b1+=(($work->work_qtt) * ($work->work_price));
                                $i1=$work->idiva;
                                $c1=$b1*$ivarate/100;
                            } else {
                                $b0+=(($work->work_qtt) * ($work->work_price));
                                $i0=$work->idiva;
                                $c0=$b0*$ivarate/100;
                            }

                            // guardamos el id del albaran para actualizar al grabar
                            $worklist[]=$work->id;

                        } else {
                            // facturar, teniendo en cuenta que tenemos que tomar los
                            // datos acumulados y el work actual            

                            // creamos el objeto
                            $invoice=new Invoice;
                            $invoice->idcompany=$idco;
                            // tomamos los datos del anterior cliente
                            $invoice->idcustomer=$idcust;
                            $pmeth=$this->getPaymentMethod($idcust);
                            $invoice->idmethod=$pmeth;
                            $invoice->inv_date= converterDateToDDBB($invdate);
                            $number=$this->generateNextInvoiceNumber($idcompany, $invdate);
                            $invoice->inv_number=$number;
                            $expiration=$this->getExpiration($pmeth,date('d-m-Y'));
                            $invoice->inv_expiration=$expiration;

                            $invoice->inv_base0=$b0;
                            $invoice->idiva0=$i0;
                            $invoice->inv_cuota0=$c0;

                            $invoice->inv_base1=$b1;
                            $invoice->idiva1=$i1;
                            $invoice->inv_cuota1=$c1;                

                            $invoice->inv_base2=$b2;
                            $invoice->idiva2=$i2;
                            $invoice->inv_cuota2=$c2;

                            $invoice->inv_base3=$b3;
                            $invoice->idiva3=$i3;
                            $invoice->inv_cuota3=$c3;

                            $invoice->inv_total=$b0+$c0+$b1+$c1+$b2+$c2+$b3+$c3;

                            // grabamos el objeto factura
                            $invoice->save();

                            // procedemos a grabar la lista de albaranes facturados 
                            // con el numero de factura al cual han sido añadidos
                            foreach ($worklist as $wk) {
                                $wrk= Work::find($wk);
                                $wrk->idinvoice=$invoice->id;
                                $wrk->save();                             
                            }
                            // creamos la lista de albaranes de cada factura
                            $worklist=array();
                            // reseteamos a cero bases y cuotas
                            $b0=$c0=$b1=$c1=$b2=$c2=$b3=$c3=0;
                            // reseteamos a uno tipo
                            $i0=$i1=$i2=$i3=1;

                            // ahora tenemos que acumular el albaran nuevo
                            $idco=$work->idcompany;
                            $iva=$this->getIvaType($work->idiva);
                            $ivatype=$iva[0];
                            $ivarate=$iva[1];

                            if ($ivatype==3) {
                                $b3+=(($work->work_qtt) * ($work->work_price));
                                $i3=$work->idiva;
                                $c3=$b3*$ivarate/100;
                            } elseif ($ivatype==2) {
                                $b2+=(($work->work_qtt) * ($work->work_price));
                                $i2=$work->idiva;
                                $c2=$b2*$ivarate/100;
                            } elseif ($ivatype==1) {
                                $b1+=(($work->work_qtt) * ($work->work_price));
                                $i1=$work->idiva;
                                $c1=$b1*$ivarate/100;
                            } else {
                                $b0+=(($work->work_qtt) * ($work->work_price));
                                $i0=$work->idiva;
                                $c0=$b0*$ivarate/100;
                            }

                            // guardamos el id del albaran para actualizar al grabar
                            $worklist[]=$work->id;

                            // y actualizamos el cliente con el cliente anterior
                            $idcust=$idcu;                        

                        }

                    }
                    // y hay que grabar el último !!!
                    // creamos el objeto
                    $invoice=new Invoice;
                    $invoice->idcompany=$idco;
                    // tomamos los datos del anterior cliente
                    $invoice->idcustomer=$idcust;
                    $pmeth=$this->getPaymentMethod($idcust);
                    $invoice->idmethod=$pmeth;
                    $invoice->inv_date= converterDateToDDBB($invdate);
                    $number=$this->generateNextInvoiceNumber($idcompany, $invdate);
                    $invoice->inv_number=$number;
                    $expiration=$this->getExpiration($pmeth,date('d-m-Y'));
                    $invoice->inv_expiration=$expiration;

                    $invoice->inv_base0=$b0;
                    $invoice->idiva0=$i0;
                    $invoice->inv_cuota0=$c0;

                    $invoice->inv_base1=$b1;
                    $invoice->idiva1=$i1;
                    $invoice->inv_cuota1=$c1;                

                    $invoice->inv_base2=$b2;
                    $invoice->idiva2=$i2;
                    $invoice->inv_cuota2=$c2;

                    $invoice->inv_base3=$b3;
                    $invoice->idiva3=$i3;
                    $invoice->inv_cuota3=$c3;

                    $invoice->inv_total=$b0+$c0+$b1+$c1+$b2+$c2+$b3+$c3;
              
                    try {   
                        // grabamos el objeto factura
                        $invoice->save();

                        // procedemos a grabar la lista de albaranes facturados 
                        // con el numero de factura al cual han sido añadidos
                        foreach ($worklist as $wk) {
                            $wrk= Work::find($wk);
                            $wrk->idinvoice=$invoice->id;
                            $wrk->save();                             
                        }  
                    } catch (Exception $ex) {
                        $errors=true;
                        $messageWrong='Error grabando facturas';
                    } catch (QueryException $quex) {
                        $errors=true;
                        $messageWrong='Error en base de datos grabando facturas - Error QI005';
                    }                     
                    
                    if ($errors===false) $messageOK='Facturación realizada correctamente';
                    $works=null;
                }                
                
                
            } else {
                // cada albarán será facturado en una factura
                
                $errors=false;
                
                if (!(is_null($works) || count($works)<1)) {
                    foreach ($works as $work) {

                        $pmeth=$this->getPaymentMethod($work->idcustomer);
                        $expiration=$this->getExpiration($pmeth,date('d-m-Y'));
                        $number=$this->generateNextInvoiceNumber($idcompany, $invdate);
                        $idco=$work->idcompany;
                        $idcu=$work->idcustomer;
                        $idme=0;

                        $iva=$this->getIvaType($work->idiva);
                        $ivatype=$iva[0];
                        $ivarate=$iva[1];

                        // reseteamos a cero bases y cuotas
                        $b0=$c0=$b1=$c1=$b2=$c2=$b3=$c3=0;
                        // reseteamos a uno tipo
                        $i0=$i1=$i2=$i3=1;

                        if ($ivatype==3) {
                            $b3=(($work->work_qtt) * ($work->work_price));
                            $i3=$work->idiva;
                            $c3=$b3*$ivarate/100;
                        } elseif ($ivatype==2) {
                            $b2=(($work->work_qtt) * ($work->work_price));
                            $i2=$work->idiva;
                            $c2=$b2*$ivarate/100;
                        } elseif ($ivatype==1) {
                            $b1=(($work->work_qtt) * ($work->work_price));
                            $i1=$work->idiva;
                            $c1=$b1*$ivarate/100;
                        } else {
                            $b0=(($work->work_qtt) * ($work->work_price));
                            $i0=$work->idiva;
                            $c0=$b0*$ivarate/100;
                        }

                        // creamos el objeto
                        $invoice=new Invoice;
                        $invoice->idcompany=$idco;
                        $invoice->idcustomer=$idcu;
                        $invoice->idmethod=$pmeth;

                        $invoice->inv_date= converterDateToDDBB($invdate);
                        $invoice->inv_number=$number;
                        $invoice->inv_expiration=$expiration;

                        $invoice->inv_base0=$b0;
                        $invoice->idiva0=$i0;
                        $invoice->inv_cuota0=$c0;

                        $invoice->inv_base1=$b1;
                        $invoice->idiva1=$i1;
                        $invoice->inv_cuota1=$c1;                

                        $invoice->inv_base2=$b2;
                        $invoice->idiva2=$i2;
                        $invoice->inv_cuota2=$c2;

                        $invoice->inv_base3=$b3;
                        $invoice->idiva3=$i3;
                        $invoice->inv_cuota3=$c3;

                        $invoice->inv_total=$b0+$c0+$b1+$c1+$b2+$c2+$b3+$c3;

                        try {   
                            // grabamos el objeto
                            $invoice->save();

                            // grabamos el albaran con el numero de factura
                            $work= Work::find($work->id);
                            $work->idinvoice=$invoice->id;
                            $work->save();
                        } catch (Exception $ex) {
                            $errors=true;
                            $messageWrong='Error grabando facturas';
                        } catch (QueryException $quex) {
                            $errors=true;
                            $messageWrong='Error en base de datos grabando facturas - Error QI006';
                        } 
                    }
                    
                    if ($errors===false) $messageOK='Facturación realizada correctamente';
                    $works=null;
                }                 
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
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI007';
            }            
            
        } else {
            $messageWrong='Empresa no corresponde al usuario';
                // generamos un objeto en blanco
                $works=null;
                $customers=null;
        }   
                        
        return view('invoices/invoicesMenu')
            ->with('works',$works)
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);         
        
    }
    
    
    /**
     * Esta función muestra por pantalla el menú de listados de facturas
     * @return type
     */
    public function showInvoicesMenu($mess='') {
        
        // mensajes
        $messageOK=$messageWrong=null;

        // recuperamos el mensaje, si lo trae
        if (strlen($mess)>1) $messageOK=$mess;
        
        // parametros de búsqueda
        $parameters=['cust'=>0,'fechini'=> '',
            'fechfin'=> '','amount'=>'','invnumber'=>''];
        
        $invoices=null;
      
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
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
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI008';
        }         
                        
        return view('invoices/invoicesListBySelection')
            ->with('invoices',$invoices)
            ->with('customersSel',$customers)
            ->with('parameters',$parameters) 
            ->with('totalList',0)                 
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);         
        
    }
    
    
    /**
     * Esta función obtiene la selección para el listado de facturas, a partir
     * del formulario correspondiente, y muestra el listado en la misma
     * pantalla de selección.
     * 
     * @param Request $request
     * @return type
     */
    public function invoicesList(Request $request) {
        
        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('idcustomer'));        
        $invnumber= clearInput($request->input('invnumber'));
        $fechini= clearInput($request->input('fechini'));
        $fechfin= clearInput($request->input('fechfin'));
        $amount= clearInput($request->input('amount'));
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['cust'=>$idcustomer,'fechini'=> $fechini,
            'fechfin'=> $fechfin,'amount'=>$amount,'invnumber'=>$invnumber];        
        
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
        if (strlen($invnumber)<1) $inv='';
        else $inv=$invnumber;
        
        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) $idcustomer='%%';        
        
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // verificamos que el usuario pertenece a la empresa
        if ($idcompany == $idcomp) {

            try {

                // si amount es cero, es que no se filtra por cantidad
                // y entonces buscamos también negativos
                if ($amount==0) $amount=-999999999;                
                
                // buscamos en DDBB
                $invoices= Invoice::where([
                 ['invoices.idcustomer','LIKE',$idcustomer],
                 ['invoices.idcompany',$idcompany],
                 ['invoices.inv_date','>=',$fechini],
                 ['invoices.inv_date','<',$fechfin],
                 ['invoices.inv_total','>=',$amount],
                 ['invoices.inv_number','LIKE','%'.$inv.'%']              
                 ])
                     ->leftJoin('customers','customers.id','invoices.idcustomer')
                     ->select('invoices.*','customers.customer_name as name')
                     ->orderBy('invoices.inv_number')
                     ->get();         

                $totalList = Invoice::where([
                 ['invoices.idcustomer','LIKE',$idcustomer],
                 ['invoices.idcompany',$idcompany],
                 ['invoices.inv_date','>=',$fechini],
                 ['invoices.inv_date','<',$fechfin],
                 ['invoices.inv_total','>=',$amount],
                 ['invoices.inv_number','LIKE','%'.$inv.'%']              
                 ])
                     ->select('invoices.inv_total')
                     ->sum('invoices.inv_total');                
                
            } catch (Exception $ex) {

                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;
                $messageWrong='Error en base de datos obteniendo albaranes';

            } catch (QueryException $quex) {

                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;            
                $messageWrong='Error en base de datos obteniendo albaranes';

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
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI009';
            }            
            
        } else {
            $messageWrong='Empresa no corresponde al usuario';
                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;
        }   
                        
        return view('invoices/invoicesListBySelection')
            ->with('invoices',$invoices)
            ->with('customersSel',$customers)
            ->with('parameters',$parameters)   
            ->with('totalList',$totalList)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);         
        
    }
   
    
    /**
     * Esta función obtiene la selección para el listado de facturas, a partir
     * del formulario correspondiente, y muestra el listado en la misma
     * pantalla de selección.
     * 
     * @param Request $request
     * @return type
     */
    public function invoicesPdfList(Request $request) {
        
        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('idcustomer'));        
        $invnumber= clearInput($request->input('invnumber'));
        $fechini= clearInput($request->input('fechini'));
        $fechfin= clearInput($request->input('fechfin'));
        $amount= clearInput($request->input('amount'));
                  
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
        if (strlen($invnumber)<1) {
            $inv='';
            $textlist.=' - Todas las facturas';
        } else {
            $inv=$invnumber;
            $textlist.=' - Solo la factura seleccionada';            
        }
        
        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) {
            $idcustomer='%%';
            $textlist.=' - Todos los clientes';
        } else {
            $textlist.=' - del cliente seleccionado';
        }
        
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // verificamos que el usuario pertenece a la empresa
        if ($idcompany == $idcomp) {
            
            // si amount es cero, es que no se filtra por cantidad
            // y entonces buscamos también negativos
            if ($amount==0) $amount=-999999999;

            try {

                // buscamos en DDBB
                $invoices= Invoice::where([
                 ['invoices.idcustomer','LIKE',$idcustomer],
                 ['invoices.idcompany',$idcompany],
                 ['invoices.inv_date','>=',$fechini],
                 ['invoices.inv_date','<',$fechfin],
                 ['invoices.inv_total','>=',$amount],
                 ['invoices.inv_number','LIKE','%'.$inv.'%']              
                 ])
                     ->leftJoin('customers','customers.id','invoices.idcustomer')
                     ->select('invoices.*','customers.customer_name as name')
                     ->orderBy('invoices.inv_number')
                     ->get();         

                $totalList = Invoice::where([
                 ['invoices.idcustomer','LIKE',$idcustomer],
                 ['invoices.idcompany',$idcompany],
                 ['invoices.inv_date','>=',$fechini],
                 ['invoices.inv_date','<',$fechfin],
                 ['invoices.inv_total','>=',$amount],
                 ['invoices.inv_number','LIKE','%'.$inv.'%']              
                 ])
                     ->select('invoices.inv_total')
                     ->sum('invoices.inv_total');                
                
            } catch (Exception $ex) {

                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;
                $messageWrong='Error en base de datos obteniendo albaranes';
                // parametros de búsqueda
                $parameters=['cust'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>'','invnumber'=>''];                
                return view('invoices/invoicesListBySelection')
                    ->with('invoices',null)
                    ->with('customersSel',null)
                    ->with('parameters',$parameters) 
                    ->with('totalList',0)                 
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);                 

            } catch (QueryException $quex) {

                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;            
                $messageWrong='Error en base de datos obteniendo albaranes - Error QI010';
                // parametros de búsqueda
                $parameters=['cust'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>'','invnumber'=>''];                
                return view('invoices/invoicesListBySelection')
                    ->with('invoices',null)
                    ->with('customersSel',null)
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
                // parametros de búsqueda
                $parameters=['cust'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>'','invnumber'=>''];                
                return view('invoices/invoicesListBySelection')
                    ->with('invoices',null)
                    ->with('customersSel',null)
                    ->with('parameters',$parameters) 
                    ->with('totalList',0)                 
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);                 
        }   
                        
        // cabecera de la factura
        $data='
            <head>
                <title>Listado de facturas</title>
                <meta charset="utf-8">
                <style>
                html {
                  min-height: 100%;
                  position: relative;
                }
                th {                   
                    border:1px solid black;
                }
                td {
                    padding-left:5px;
                    padding-right:5px;
                }
                .right {
                    text-align:right;
                }
                .left {
                    text-align:left;
                }
                .center {
                    text-align:center;
                }                
                .fieldlong {
                    text-align:center;
                    width:50%;
                    font-size:0.8em;                    
                }
                .fieldshort {
                    width:15%;
                    font-size:0.8em;                    
                }                
                </style>
            </head>
            <body style="width:1000px;">
            
                <div style="width:100%; margin: 0px 5px 5px 0px;border:1px solid black">

                <h2>Listado de facturas</h2>
                <p>'.$textlist.'</p>
                </div>';
        
        // cuerpo de la factura
        $data.='
            <div style="min-height:500px;border:1px solid black" >
                <table>
                    <tr>
                        <th class="fieldshort">Nº factura</th>
                        <th class="fieldlong">Cliente</th>
                        <th class="fieldshort">Fecha</th>
                        <th class="fieldshort">Importe</th>
                        <th class="fieldshort">Vencimiento</th>
                    </tr>';
        
        $count=0;
        if (isset($invoices) && count($invoices)>0) {
            foreach ($invoices as $invoice) {
                $data.='
                    <tr>                       
                       <td class="fieldshort">'.substr($invoice->inv_number,0,15).'</td>
                       <td class="fieldlong left">'.substr($invoice->name,0,25).'</td>
                       <td class="fieldshort center">'.converterDate($invoice->inv_date).'</td>
                       <td class="fieldshort right">'.number_format($invoice->inv_total,2,',','.').'</td>
                       <td class="fieldshort center">'.converterDate($invoice->inv_expiration).'</td>
                    </tr>';
                $count++;
                // paginamos
                if ($count>=45) {
                    // pie de la factura
                    $count=0;
                    $data.='</table></div><br /><br />

                    <div style="width:100%; margin: 0px 5px 5px 0px;border:1px solid black">
                    <h2>Listado de facturas</h2>
                    <p>'.$textlist.'</p>
                    </div>
                    <div style="min-height:500px;border:1px solid black" >
                        <table>
                            <tr>
                                <th class="fieldshort">Nº factura</th>
                                <th class="fieldlong">Cliente</th>
                                <th class="fieldshort">Fecha</th>
                                <th class="fieldshort">Importe</th>
                                <th class="fieldshort">Vencimiento</th>
                            </tr>';             
                }            
            }
            // cerramos tabla
            $data.='</table></div>';
            
        } else {
            $data.='</table>            
                <div style="width:100%;" >                        
                   <input type="text" value="No se ha obtenido ningún dato" style="width:100%;height:50px;border:none;" >
                </div>
            </div>';                 
        }        

        $data.='
            <br />
            <hr>
            <br />
            <div style="width:100%;font-weight:bold;font-size:1.4em;" >                
                <input type="text" value="Total importes ...: '. number_format($totalList,2,',','.').' euros" 
                    style="width:50%;height:50px;text-align:center;border:none" >
            </div>';
     
        // generamos un pdf en vista directa sobre la pantalla actual
        $this->generatePdfDocumentInvoice($data, false, true);
        return;          
        
    }
    
    
    
        
    /**
     * Esta función muestra por pantalla una factura identificada por su id
     * @return type
     */
    public function showInvoice($id) {

        // mensajes
        $messageOK=$messageWrong=null;
      
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        $invoice=new Invoice;
        
        try {   
            // obtenemos la factura correspondiente al id
            $invoice= Invoice::where([
                ['id',$id],
                ['idcompany',$idcomp]
            ])->first();
            
            // obtenemos el cliente de la factura
            $customer= Customer::find($invoice->idcustomer);
            
            //obtenemos la lista de albaranes de esa factura
            $works= Work::where([
                ['idinvoice',$invoice->id],
                ['idcompany',$idcomp]                
            ])->get();
            
            // obtenemos los clientes de la empresa
            $customers= Customer::where('idcompany',$idcomp)
                ->orderBy('customer_name')                    
                ->get();
            
            // obtenemos los tipos de iva
            $ivaRates= IvaRates::where([
                ['idcompany',$idcomp],
                ['active',true]
            ])->get();
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI011';
        }         
                        
        return view('invoices/invoice')
            ->with('invoice',$invoice)
            ->with('works',$works)
            ->with('customers',$customers)
            ->with('ivaRates',$ivaRates)                
            ->with('customerSelected',$customer)
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);         
        
    }
    
    
    /**
     * Esta función borra una factura seleccionada desde el formulario, y libera
     * los albaranes que contenía dejándolos como pendientes
     * @param Request $request
     * @return type
     */
    public function deleteInvoice(Request $request) {
      
        // mensajes
        $messageOK=$messageWrong=null;
            
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        // obtenemos los parametros del formulario
        $idcompany= clearInput($request->input('companyid'));
        $idinvoice= clearInput($request->input('invoiceid'));               
        
        if ($idcomp == $idcompany) {
            try {   
                // obtenemos la factura correspondiente al id
                $invoice= Invoice::where([
                    ['id',$idinvoice],
                    ['idcompany',$idcomp]
                ])->first();
                
                if (!(is_null($invoice) || $invoice===false) ) {

                    //obtenemos la lista de albaranes de esa factura
                    $works= Work::where([
                        ['idinvoice',$idinvoice],
                        ['idcompany',$idcomp]                
                    ])->get();

                    // procedemos a borrar el id de factura en el albarán
                    // así quedará como pendiente de facturar
                    foreach ($works as $work) {
                        $work->idinvoice=0;
                        $work->save();
                    }

                    // ahora borramos la factura
                    $invoice->delete();

                    // obtenemos los clientes de la empresa
                    $customers= Customer::where('idcompany',$idcomp)
                        ->orderBy('customer_name')                            
                        ->get();

                    // obtenemos los tipos de iva
                    $ivaRates= IvaRates::where([
                        ['idcompany',$idcomp],
                        ['active',true]
                    ])->get();

                    $messageOK='Factura borrada correctamente';

                } else {
                    // el id de la factura no es correcto
                    $messageWrong='El id de la factura no existe.';
                }                                
            } catch (Exception $ex) {
                $messageWrong='Error procesando el borrado de una factura';

            } catch (QueryException $quex) {
                $messageWrong='Error en base de datos procesando el borrado de una factura - Error QI012';

            }            
        } else {
            $messageWrong='El usuario no pertenece a la empresa';
        }             
        
        return redirect()->action('InvoiceController@showInvoicesMenu',['mess'=>$messageOK]);
    }
    
    
    /**
     * Esta función genera una vista por pantalla de un documento PDF, en la misma
     * pantalla (_self) donde se estaba consultando la factura
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function showPdfInvoice(Request $request,$id=0) {
        
        // mensajes
        $messageOK=$messageWrong=null;
      
        // tomamos el id de la factura por formulario, si es que estamos en edición
        // individual de la factura
        if ($request->has('invoiceid') && clearInput($request->input('invoiceid'))>0) 
            $id=clearInput($request->input('invoiceid'));
        
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;        
        
        $invoice=new Invoice;
        
        try {
            // obtenemos los datos de la empresa
            $company=Company::find($idcomp);
            
            // obtenemos la factura correspondiente al id
            $invoice= Invoice::where([
                ['id',$id],
                ['idcompany',$idcomp]
            ])->first();
            
            // obtenemos los tipos de iva de la factura
            $rate0= IvaRates::where([
                ['idcompany',$idcomp],
                ['id',$invoice->idiva0]
            ])->first()->rate;
            $rate1= IvaRates::where([
                ['idcompany',$idcomp],
                ['id',$invoice->idiva1]
            ])->first()->rate;
            $rate2= IvaRates::where([
                ['idcompany',$idcomp],
                ['id',$invoice->idiva2]
            ])->first()->rate;
            $rate3= IvaRates::where([
                ['idcompany',$idcomp],
                ['id',$invoice->idiva3]
            ])->first()->rate;
            
            // obtenemos el cliente de la factura
            $customer= Customer::find($invoice->idcustomer);
            
            //obtenemos la lista de albaranes de esa factura
            $works= Work::where([
                ['works.idinvoice',$invoice->id],
                ['works.idcompany',$idcomp]                
            ])
                ->leftJoin('iva_rates','iva_rates.id','works.idiva')
                ->select('works.*','iva_rates.rate as ivaRate')
                    ->get();            

        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo factura pdf';
            // parametros de búsqueda
            $parameters=['cust'=>0,'fechini'=> '',
                'fechfin'=> '','amount'=>'','invnumber'=>''];                
            return view('invoices/invoicesListBySelection')
                ->with('invoices',null)
                ->with('customersSel',null)
                ->with('parameters',$parameters) 
                ->with('totalList',0)                 
                ->with('messageOK',null)
                ->with('messageWrong',$messageWrong);             
            
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo factura pdf - Error QI013';
                // parametros de búsqueda
                $parameters=['cust'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>'','invnumber'=>''];                
                return view('invoices/invoicesListBySelection')
                    ->with('invoices',null)
                    ->with('customersSel',null)
                    ->with('parameters',$parameters) 
                    ->with('totalList',0)                 
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);             
        }        
               
        // cabecera de la factura
        $data='
            <head>
                <title>Mostrar factura</title>
                <meta charset="utf-8">
                <style>
                html {
                  min-height: 100%;
                  position: relative;
                }
                h1 {
                    font-size:18px;                
                }
                h3 {
                    font-size:14px;
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
            <body style="width:1000px;border:1px solid black">
            
                <div style="width:99%; margin: 5px 5px 5px 5px;">

                    <div style="width:100%;border:1px solid black" >

                        <div style="width:50%"> 
                            <h1 style="margin: 0;">'.$company->company_name.'</h1>
                            <h3 style="margin: 0;">'.$company->company_address.'</h3>
                            <h3 style="margin: 0;">'.$company->company_zip.' - '.$company->company_city.'</h3>
                            <h3 style="margin: 0;">'.$company->company_nif.'</h3>
                        </div>

                        <div style="margin-left:70%;"> 
                            <label> Cliente:</label> <br/>
                            <label>'.$customer->customer_name.'</label><br/>
                            <label>'.$customer->customer_address.'</label><br/>
                            <label>'.$customer->customer_zip.' - '.$customer->customer_city.'</label><br/>
                            <label>'.$customer->customer_nif.'</label><br/>
                        </div>

                        <hr>
                        <br />

                        <div style="width:95%; margin: 5px 5px 5px 5px;font-size:1.2em;font-weight:bold; display:inline-block">
                            <label>Factura '.$invoice->inv_number.'   -  Fecha Factura '.converterDate($invoice->inv_date).'</label>
                        </div>

                    </div>';
        
        // cuerpo de la factura
        $data.='
            <div style="min-height:500px;" >
                <table>
                    <tr>
                        <th>Código</th>
                        <th class="fieldshort">Uds</th>
                        <th class="fieldlong">Concepto</th>
                        <th class="fieldshort">% Iva</th>
                        <th class="fieldshort">Precio</th>
                        <th>Importe</th>                        
                    </tr>';
            $count=0;
            foreach ($works as $work) {
                $count++;
                $data.='
                    <tr>                       
                       <td> <span> -- </span></td>
                       <td> <span>'.number_format($work->work_qtt,2,',','.').'</span></td>
                       <td> <span>'.substr($work->work_text,0,42).'</span></td>
                       <td> <span>'.number_format($work->ivaRate,2,',','.').' </span></td>
                       <td> <span>'.number_format($work->work_price,2,',','.').'</span></td>
                       <td> <span>'.number_format(($work->work_qtt*$work->work_price),2,',','.').'</span></td>
                    </tr>';
                // si el concepto excede de 51 chars., hacemos varias líneas
                if (strlen($work->work_text) > 42) {
                    $conceptlength= strlen($work->work_text);
                    for ($n=42;$n<$conceptlength;$n=$n+42) {
                        $count++;
                        // paginamos líneas de 51 caracteres de longitud
                        $data.='<tr>
                           <td> <span> -- </span></td>
                           <td> <span> </span></td>
                           <td> <span>'.substr($work->work_text,$n,42).'</span></td>
                           <td> <span> </span></td>
                           <td> <span> </span></td>
                           <td> <span> </span></td>
                           </tr>';
                    }            
                }                
            }
            
            for ($n=$count;$n<14;$n++) {
                $data.='
                    <tr>                       
                       <td> <br /></td>
                       <td> <span> </span></td>
                       <td> <span> </span></td>
                       <td> <span> </span></td>
                       <td> <span> </span></td>
                       <td> <span> </span></td>
                    </tr>';                
            }            
            $data.='</table></div>';
        
        // resumen importe de factura
        // los desgloses de cuotas se muestran si la factura tiene 2 o más tipos de iva
        $counttypes=0;
        $data2='<br />';        
        if ($invoice->inv_base0>0) {
            $data2.='
            <div style="width:100%;" >
                <label for="bimp0">Base Imponible al '.number_format((100*$invoice->inv_cuota0/$invoice->inv_base0),2,',','.').' % ..:</label>
                <input type="text" name="bimp0" value="'.number_format($invoice->inv_base0,2,',','.').' €" style="width:20%;height:40px; border:none" >
                <label for="tipo0">Tipo IVA</label>                  
                <input type="text" name="tipo0" value="'.number_format($rate0,2,',','.').' %" style="width:20%;height:40px; border:none" >                    
                <label for="cuot0">Cuota IVA</label>                  
                <input type="text" name="cuot0" value="'.number_format($invoice->inv_cuota0,2,',','.').' €" style="width:20%;height:40px; border:none" >
            </div>';
            $counttypes++;
        }
        if ($invoice->inv_cuota1>0) {
            $data2.='
            <div style="width:100%;" >
                <label for="bimp1">Base Imponible al '.number_format((100*$invoice->inv_cuota1/$invoice->inv_base1),2,',','.').' % ..:</label>
                <input type="text" name="bimp1" value="'.number_format($invoice->inv_base1,2,',','.').' €" style="width:20%;height:40px; border:none" >
                <label for="tipo1">Tipo IVA</label>                  
                <input type="text" name="tipo1" value="'.number_format($rate1,2,',','.').' %" style="width:20%;height:40px; border:none" >                     
                <label for="cuot1">Cuota IVA</label>                  
                <input type="text" name="cuot1" value="'.number_format($invoice->inv_cuota1,2,',','.').' €" style="width:20%;height:40px; border:none" >
            </div>';
            $counttypes++;
        }
        if ($invoice->inv_cuota2>0) {
            $data2.='
            <div style="width:100%;" >
                <label for="bimp2">Base Imponible al '.number_format((100*$invoice->inv_cuota2/$invoice->inv_base2),2,',','.').' % ..:</label>
                <input type="text" name="bimp2" value=" '.number_format($invoice->inv_base2,2,',','.').' €" style="width:20%;height:40px; border:none" >
                <label for="tipo2">Tipo IVA</label>                  
                <input type="text" name="tipo2" value="'.number_format($rate2,2,',','.').' %" style="width:20%;height:40px; border:none" >                   
                <label for="cuot2">Cuota IVA</label>                  
                <input type="text" name="cuot2" value="'.number_format($invoice->inv_cuota2,2,',','.').' €" style="width:20%;height:40px; border:none" >
            </div>';
            $counttypes++;            
        }
        if ($invoice->inv_cuota3>0) {
            $data2.='
            <div style="width:100%;" >
                <label for="bimp3">Base Imponible al '.number_format((100*$invoice->inv_cuota3/$invoice->inv_base3),2,',','.').' % ..:</label>
                <input type="text" name="bimp3" value="'.number_format($invoice->inv_base3,2,',','.').' €" style="width:20%;height:40px; border:none">
                <label for="tipo3">Tipo IVA</label>                  
                <input type="text" name="tipo3" value="'.number_format($rate3,2,',','.').' %" style="width:20%;height:40px; border:none" >                     
                <label for="cuot3">Cuota IVA</label>                  
                <input type="text" name="cuot3" value="'.number_format($invoice->inv_cuota3,2,',','.').' €" style="width:20%;height:40px; border:none" >
            </div>';
            $counttypes++;            
        }        
        
        // si hay dos o más tipos de iva, se muestra el desglose
        if ($counttypes>1) {
            $data.=$data2;            
        }
        
        $bimp=$invoice->inv_base0+$invoice->inv_base1+$invoice->inv_base2+$invoice->inv_base3;
        $cuota=$invoice->inv_cuota0+$invoice->inv_cuota1+$invoice->inv_cuota2+$invoice->inv_cuota3;
        
        $data.='
            <br />
            <hr>
            <div style="width:100%;font-size:0.85em;font-weight:bold" >
                <label for="bimp">Base Imponible</label>
                <input type="text" name="bimp" value="'.number_format($bimp,2,',','.').'" 
                    style="width:19%;height:40px;border:2px solid black;text-align:center" >
                <label for="cuot" style="margin-left:20px" >Cuota IVA</label>                    
                <input type="text" name="cuot" value="'.number_format($cuota,2,',','.').'" 
                    style="width:12%;height:40px;border:2px solid black;text-align:center" >    
                <label for="ttl" style="margin-left:70px">Total Factura</label>                     
                <input type="text" name="ttl" value="'. number_format($invoice->inv_total,2,',','.').'" 
                    style="width:19%;height:50px;border:2px solid black;text-align:center" >
            </div>
            <br />
            <div style="width:100%" >               
                <input type="text" value="Vencimiento de factura: '.converterDate($invoice->inv_expiration).'" 
                    style="width:55%;height:50px;text-align:left;border:none" >
                <input type="text" value="importe total a pagar...: '. number_format($invoice->inv_total,2,',','.').' €" 
                    style="width:40%;height:50px;text-align:center;border:none" >
            </div>';
     
        // pie de la factura
        
        // obtenemos el pie de las configuraciones de empresa        
        $pie=$this->getInvoiceFooter($idcomp);
        
        $data.='      
            </div>
        </body>';
        
        // generamos un pdf en vista directa sobre la pantalla actual
        $this->generatePdfDocumentInvoice($data, $pie, false);
        
        return;
                
    }
    
 
    
    /**
     * Esta función muestra el menú de listado de sumatorio de facturas emitidas
     * a los clientes entre fechas
     * @return type
     */
    public function customerInvoicesMenu() {
                
        // mensajes
        $messageOK=$messageWrong=null;       
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['cust'=>0,'fechini'=> '','fechfin'=> ''];        
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
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
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI015';
        }          
        
        return view('invoices/customerInvoicesSum')
            ->with('customersSel',$customers)
            ->with('invoices',null)
            ->with('parameters',$parameters)                
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);  
        
    }
    
    
    
    /**
     * Esta función muestra el menú de listado de sumatorio de facturas emitidas
     * a los clientes entre fechas
     * @return type
     */
    public function showSumatoryInvoices(Request $request) {
                
        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('idcustomer'));        
        $fechini= clearInput($request->input('fechini'));
        $fechfin= clearInput($request->input('fechfin'));
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['cust'=>$idcustomer,'fechini'=> $fechini,
            'fechfin'=> $fechfin];
        
        // FECHA INICIAL DEL LISTADO (por defecto, año en curso)
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

        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) {
            $idcustomer='%%';
            $textlist.=' - Todos los clientes';
        } else {
            $textlist.=' - del cliente seleccionado';
        }
        
        
        $invoices=null;
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // variables acumuladas
        $sumtot=$sumbas0=$sumcuo0=$sumbas1=$sumcuo1=$sumbas2=$sumcuo2=$sumbas3=$sumcuo3=0;  
        
        if ($idcompany == $idcomp) {
            
            try {      
                
                // obtenemos la lista de ids y nombre de clientes con facturación
                // en el intervalo deseado
                $customersList= Invoice::where([
                 ['invoices.idcustomer','LIKE',$idcustomer],
                 ['invoices.idcompany',$idcompany],
                 ['invoices.inv_date','>=',$fechini],
                 ['invoices.inv_date','<',$fechfin]              
                 ])
                     ->leftJoin('customers','customers.id','invoices.idcustomer')                       
                     ->select('invoices.idcustomer','customers.customer_name as name')
                     ->groupBy('invoices.idcustomer','customers.customer_name')                        
                     ->orderBy('customers.customer_name')
                     ->get();

                // procesamos la lista, sumando cada base, cuota y el total,
                // cliente por cliente
                
                foreach ($customersList as $custom) {
                    
                    $totalList = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')
                        ->sum('invoices.inv_total');
                    $base0List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_base0');
                    $base1List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_base1');
                    $base2List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_base2');
                    $base3List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_base3');          
                    $cuota0List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_cuota0');
                    $cuota1List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_cuota1');
                    $cuota2List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]            
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_cuota2');
                    $cuota3List = Invoice::where([
                     ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                     ['invoices.idcompany',$idcompany],
                     ['invoices.inv_date','>=',$fechini],
                     ['invoices.inv_date','<',$fechfin]        
                     ])
                         ->select('invoices.*','invoices.idcustomer')
                         ->groupBy('invoices.idcustomer')                    
                        ->sum('invoices.inv_cuota3');                    
                                                            
                    // guardamos los datos de cada cliente en un array para visualizar en pantalla
                    $arr=array('id'=>$custom['idcustomer'],'name'=>$custom['name'],'total'=>$totalList,
                        'base0'=>$base0List,'base1'=>$base1List,'base2'=>$base2List,'base3'=>$base3List,
                        'cuota0'=>$cuota0List,'cuota1'=>$cuota1List,'cuota2'=>$cuota2List,'cuota3'=>$cuota3List);
                    $invoices[]=$arr;
                    
                    // acumulamos los diferentes subtotales
                    $sumtot+=$totalList;
                    $sumcuo0+=$cuota0List;
                    $sumbas0+=$base0List;
                    $sumcuo1+=$cuota1List;
                    $sumbas1+=$base1List;
                    $sumcuo2+=$cuota2List;
                    $sumbas2+=$base2List;
                    $sumcuo3+=$cuota3List;
                    $sumbas3+=$base3List;                      
                    
                }                
                
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error generando listado resumen facturación de clientes';                 
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error en base de datos generando listado resumen facturación de clientes - Error QI016'; 
            }
            
        } else {            
            $messageWrong='El usuario no pertenece a la empresa del listado';           
        }
        
        
        if (is_null($invoices) || count($invoices)<1) $messageWrong='No se han obtenido resultados en la búsqueda';
        
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
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa - Error QI017';
        }          
        
        
        
        $totals=['base0'=>$sumbas0,'cuota0'=> $sumcuo0,
            'base1'=>$sumbas1,'cuota1'=> $sumcuo1,
            'base2'=>$sumbas2,'cuota2'=> $sumcuo2,
            'base3'=>$sumbas3,'cuota3'=> $sumcuo3,
            'total'=>$sumtot];
        
        return view('invoices/customerInvoicesSum')
            ->with('customersSel',$customers)
            ->with('invoices',$invoices)
            ->with('parameters',$parameters) 
            ->with('totals',$totals)                 
            ->with('messageOK',$messageOK)
            ->with('messageWrong',$messageWrong);          
    }
    
    
    /**
     * Esta función muestra el menú de listado de sumatorio de facturas emitidas
     * a los clientes entre fechas
     * @return type
     */
    public function sumatoryInvoicesPdf(Request $request) {
                
        // mensajes
        $messageOK=$messageWrong=null;
        
        // lectura de parametros de formulario
        $idcompany= clearInput($request->input('companyid'));
        $idcustomer= clearInput($request->input('idcustomer'));        
        $fechini= clearInput($request->input('fechini'));
        $fechfin= clearInput($request->input('fechfin'));
        
        // guardamos los parametros del formulario para 
        // mostrarlos de regreso al formulario
        $parameters=['cust'=>$idcustomer,'fechini'=> $fechini,
            'fechfin'=> $fechfin];
        
        // FECHA INICIAL DEL LISTADO (por defecto, año en curso)
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

        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) {
            $idcustomer='%%';
            $textlist.=' - Todos los clientes';
        } else {
            $textlist.=' - del cliente seleccionado';
        }
        
        
        $invoices=null;
        
        // compañia del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        if ($idcompany == $idcomp) {
            
            try {      
                
                // obtenemos la lista de clientes 
                $customersList= Invoice::where([
                 ['invoices.idcustomer','LIKE',$idcustomer],
                 ['invoices.idcompany',$idcompany],
                 ['invoices.inv_date','>=',$fechini],
                 ['invoices.inv_date','<',$fechfin]              
                 ])
                     ->leftJoin('customers','customers.id','invoices.idcustomer')                       
                     ->select('invoices.idcustomer','customers.customer_name as name')
                     ->groupBy('invoices.idcustomer','customers.customer_name')                        
                     ->orderBy('customers.customer_name')
                     ->get();
                
                // variables acumuladas
                $sumtot=$sumbas0=$sumcuo0=$sumbas1=$sumcuo1=$sumbas2=$sumcuo2=$sumbas3=$sumcuo3=0;
                if (isset($customersList) && count($customersList)>0) {
                    foreach ($customersList as $custom) {

                        $totalList = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')
                            ->sum('invoices.inv_total');
                        $base0List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_base0');
                        $base1List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_base1');
                        $base2List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_base2');
                        $base3List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_base3');          
                        $cuota0List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_cuota0');
                        $cuota1List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_cuota1');
                        $cuota2List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]            
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_cuota2');
                        $cuota3List = Invoice::where([
                         ['invoices.idcustomer','LIKE',$custom['idcustomer'] ],
                         ['invoices.idcompany',$idcompany],
                         ['invoices.inv_date','>=',$fechini],
                         ['invoices.inv_date','<',$fechfin]        
                         ])
                             ->select('invoices.*','invoices.idcustomer')
                             ->groupBy('invoices.idcustomer')                    
                            ->sum('invoices.inv_cuota3');                    

                        // preparamos el array con los datos de cada cliente
                        $arr=array('id'=>$custom['idcustomer'],'name'=>$custom['name'],'total'=>$totalList,
                            'base0'=>$base0List,'base1'=>$base1List,'base2'=>$base2List,'base3'=>$base3List,
                            'cuota0'=>$cuota0List,'cuota1'=>$cuota1List,'cuota2'=>$cuota2List,'cuota3'=>$cuota3List);
                        $invoices[]=$arr;

                        // acumulamos los diferentes subtotales
                        $sumtot+=$totalList;
                        $sumcuo0+=$cuota0List;
                        $sumbas0+=$base0List;
                        $sumcuo1+=$cuota1List;
                        $sumbas1+=$base1List;
                        $sumcuo2+=$cuota2List;
                        $sumbas2+=$base2List;
                        $sumcuo3+=$cuota3List;
                        $sumbas3+=$base3List;                    
                    }
                }

                
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error generando listado pdf resumen facturación de clientes';
                // parametros de búsqueda
                $parameters=['cust'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>'','invnumber'=>''];                
                return view('invoices/invoicesListBySelection')
                    ->with('invoices',null)
                    ->with('customersSel',null)
                    ->with('parameters',$parameters) 
                    ->with('totalList',0)                 
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);                
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error en base de datos generando listado pdf resumen facturación de clientes - Error QI018';
                // parametros de búsqueda
                $parameters=['cust'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>'','invnumber'=>''];                
                return view('invoices/invoicesListBySelection')
                    ->with('invoices',null)
                    ->with('customersSel',null)
                    ->with('parameters',$parameters) 
                    ->with('totalList',0)                 
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);                
            }
            
        } else {            
            $messageWrong='El usuario no pertenece a la empresa del listado';
                // parametros de búsqueda
                $parameters=['cust'=>0,'fechini'=> '',
                    'fechfin'=> '','amount'=>'','invnumber'=>''];                
                return view('invoices/invoicesListBySelection')
                    ->with('invoices',null)
                    ->with('customersSel',null)
                    ->with('parameters',$parameters) 
                    ->with('totalList',0)                 
                    ->with('messageOK',null)
                    ->with('messageWrong',$messageWrong);            
        }
        
         // cabecera de la factura
        $data='
            <head>
                <title>Listado facturación de clientes</title>
                <meta charset="utf-8">
                <style>
                html {
                  min-height: 100%;
                  position: relative;
                }    
                th {                   
                    border:1px solid black;
                }
                td {
                    padding-left:5px;
                    padding-right:5px;
                }
                .right {
                    text-align:right;
                }
                .left {
                    text-align:left;
                }                
                .boldt {
                    font-weight:bold;
                }                
                .fieldlong {
                    text-align:center;
                    width:40%;
                    font-size:0.8em;                    
                }
                .fieldshort {
                    width:15%;
                    font-size:0.8em;                    
                }            
                </style>
            </head>
            <body style="width:100%;border:1px solid black">
            
                <div style="width:99%; margin: 5px 5px 5px 5px;">

                <div style="width:100%; margin: 0px 5px 5px 0px;border:1px solid black">

                <h2>Listado de facturación por cliente</h2>
                <p>'.$textlist.'</p>
                </div>';
                       
        // cuerpo de la factura
        $data.='
            <table style="border:1px solid black;width:100%">
                <tr>   
                        <td class="fieldlong left boldt"><label>Cliente</label></td>
                        <td class="fieldshort right boldt"><label>Base</label></td>
                        <td class="fieldshort right boldt"><label>Cuota</label></td>  
                        <td class="fieldshort right boldt"><label>Base</label></td>
                        <td class="fieldshort right boldt"><label>Cuota</label></td>  
                        <td class="fieldshort right boldt"><label>Base</label></td>
                        <td class="fieldshort right boldt"><label>Cuota</label></td>  
                        <td class="fieldshort right boldt"><label>Base</label></td>
                        <td class="fieldshort right boldt"><label>Cuota</label></td>                        
                        <td class="fieldshort right boldt"><label>Total</label></td>

                </tr>
                <tr>
                        <td class="fieldlong left boldt"><label></label></td>
                        <td class="fieldshort right boldt"><label>Exenta</label></td>
                        <td class="fieldshort right boldt"><label>Exenta</label></td>
                        <td class="fieldshort right boldt"><label>Superred.</label></td>
                        <td class="fieldshort right boldt"><label>Superred.</label></td>
                        <td class="fieldshort right boldt"><label>Reducida</label></td>
                        <td class="fieldshort right boldt"><label>Reducida</label></td>
                        <td class="fieldshort right boldt"><label>General</label></td> 
                        <td class="fieldshort right boldt"><label>General</label></td>                       
                        <td class="fieldshort right boldt"><label>Facturación</label></td>
                </tr>';        
        $count=0;
        if (!is_null($invoices)) {
            foreach ($invoices as $invoice) {
                $data.='
                        <tr>                     
                            <td class="fieldlong left"><label>'.substr($invoice['name'],0,22).'</label ></td>
                            <td class="fieldshort right"><label>'.number_format($invoice['base0'],2,',','.').'</label ></td>
                            <td class="fieldshort right"><label>'.number_format($invoice['cuota0'],2,',','.').'</label ></td> 
                            <td class="fieldshort right"><label>'.number_format($invoice['base1'],2,',','.').'</label ></td>
                            <td class="fieldshort right"><label>'.number_format($invoice['cuota1'],2,',','.').'</label ></td> 
                            <td class="fieldshort right"><label>'.number_format($invoice['base2'],2,',','.').'</label ></td>
                            <td class="fieldshort right"><label>'.number_format($invoice['cuota2'],2,',','.').'</label ></td> 
                            <td class="fieldshort right"><label>'.number_format($invoice['base3'],2,',','.').'</label ></td>
                            <td class="fieldshort right"><label>'.number_format($invoice['cuota3'],2,',','.').'</label ></td>                        
                            <td class="fieldshort right"><label>'.number_format($invoice['total'],2,',','.').'</label ></td>
                        </tr>';
                $count++;
                // paginamos
                if ($count>=35) {
                    // pie de la factura
                    $count=0;
                    $data.='</div></table><br />

                    <div style="width:100%; margin: 0px 5px 5px 0px;border:1px solid black">
                    <h2>Listado de facturación por cliente</h2>
                    <p>'.$textlist.'</p>
                    </div>
                    <table style="border:1px solid black;width:100%">
                        <tr>   
                                <td class="fieldlong left boldt"><label>Cliente</label></td>
                                <td class="fieldshort right boldt"><label>Base</label></td>
                                <td class="fieldshort right boldt"><label>Cuota</label></td>  
                                <td class="fieldshort right boldt"><label>Base</label></td>
                                <td class="fieldshort right boldt"><label>Cuota</label></td>  
                                <td class="fieldshort right boldt"><label>Base</label></td>
                                <td class="fieldshort right boldt"><label>Cuota</label></td>  
                                <td class="fieldshort right boldt"><label>Base</label></td>
                                <td class="fieldshort right boldt"><label>Cuota</label></td>                        
                                <td class="fieldshort right boldt"><label>Total</label></td>

                        </tr>
                        <tr>
                                <td class="fieldlong left boldt"><label></label></td>
                                <td class="fieldshort right boldt"><label>Exenta</label></td>
                                <td class="fieldshort right boldt"><label>Exenta</label></td>
                                <td class="fieldshort right boldt"><label>Superred.</label></td>
                                <td class="fieldshort right boldt"><label>Superred.</label></td>
                                <td class="fieldshort right boldt"><label>Reducida</label></td>
                                <td class="fieldshort right boldt"><label>Reducida</label></td>
                                <td class="fieldshort right boldt"><label>General</label></td> 
                                <td class="fieldshort right boldt"><label>General</label></td>                       
                                <td class="fieldshort right boldt"><label>Facturación</label></td>
                        </tr>';              
                }            
            }
        } else {
            $data.='<tr>                     
                        <td colspan="10" style="text-align:left"><label>No se han obtenido datos</label ></td>
                          
                    </tr>';            
        }
        $data.='
                <tr>
                    <td colspan="10"><hr></td>
                </tr>
                <tr>                     
                    <td class="fieldshort right boldt"><label>Sumas .........</label ></td>
                    <td class="fieldshort right boldt"><label>'.number_format($sumbas0,2,',','.').'</label ></td>
                    <td class="fieldshort right boldt"><label>'.number_format($sumcuo0,2,',','.').'</label ></td> 
                    <td class="fieldshort right boldt"><label>'.number_format($sumbas1,2,',','.').'</label ></td>
                    <td class="fieldshort right boldt"><label>'.number_format($sumcuo1,2,',','.').'</label ></td> 
                    <td class="fieldshort right boldt"><label>'.number_format($sumbas2,2,',','.').'</label ></td>
                    <td class="fieldshort right boldt"><label>'.number_format($sumcuo2,2,',','.').'</label ></td> 
                    <td class="fieldshort right boldt"><label>'.number_format($sumbas3,2,',','.').'</label ></td>
                    <td class="fieldshort right boldt"><label>'.number_format($sumcuo3,2,',','.').'</label ></td>                        
                    <td class="fieldshort right boldt"><label>'.number_format($sumtot,2,',','.').'</label ></td>
                </tr>
                </div></table>';
        
        // generamos un pdf en vista directa sobre la pantalla actual
        $this->generatePdfDocument($data,true);
        
        return;
        
    }
    
    
    
    /**
     * Esta funcion obtiene el siguiente número de factura.
     * El formato del número que devuelve es el siguiente:
     * $serial(max 4 cifras) si lo tiene / año(4 cifras) - mes(2 cifras) - número consecutivo(relleno a 15) 
     * Ejemplo con serial una factura de octubre 2018: AB/201810000016
     * Ejemplo sin serial una factura de junio 2018: 201806000000023 
     * @param type $idcompany
     */
    private function generateNextInvoiceNumber($idcompany,$thisdate) {
        
        // primeramente hay que conocer el tipo de numero de factura:
        // 1 - formato aaaamm ; 2 -formato solo número
        $typeprefix=2;
        
        // si tiene serial, hay que tomarlo. El serial son alfanumerico hasta 3 chars
        // por defecto empty
        $serial='';
        
        // también hay que conocer la longitud del número de factura
        // que por defecto es 15, pero se admite entre 12 y 20
        $numLength=15;
        
        try {
            // buscar invoiceNote de la empresa
            $prefix= Config::where([
                ['idcompany',$idcompany],
                ['name','invoicePrefix']
            ])->first();
            
            if (is_null($prefix)) $typeprefix=2;
            
            // devuelve el contenido de invoiceNote
            $typeprefix=$prefix->value;

            // buscar invoicenumLength de la empresa
            $num= Config::where([
                ['idcompany',$idcompany],
                ['name','invoicenumLength']
            ])->first();
            
            if (is_null($num)) {
                $numLength=15;
            } else {
                // devuelve el contenido de invoicenumLength, con limites 12 y 15 - default 15
                if ($num->value<12) {
                    $numLength=12;                
                } elseif ($num->value>15) {
                    $numLength=15; 
                } else {
                    $numLength=$num->value;
                }
            }
            
            // buscar invoiceSerial de la empresa
            $ser= Config::where([
                ['idcompany',$idcompany],
                ['name','invoiceSerial']
            ])->first();
            
            if (is_null($ser)) $serial='';
            
            // devuelve el contenido de invoiceSerial
            $serial=$ser->value;

            
        } catch (Exception $ex) {
            // error
            $typeprefix=2;
            $numLength=15;
            $serial='';
        } catch (QueryException $quex) {
            // error
            $typeprefix=2;
            $numLength=15;
            $serial='';
        }        
        
        
        if ($typeprefix==1 || $typeprefix=="1") {

            // el serial esta separado por barras. Hay que buscar
            // primero todos los números que correspondan con el año-mes
            // de thisdate
            // FORMATO FACTURA: SERIAL/YYYYMM000000001 (total debe medir 15)
            $search=substr($thisdate,6,4).substr($thisdate,3,2);

            $invoices= Invoice::where([
                ['inv_number','LIKE','%'.$search.'%'],
                ['idcompany',$idcompany]
            ])
                ->select('inv_number')
                ->orderBy('inv_number','DESC')->first();

            if (is_null($invoices)) {
                // no hay ninguna factura en ese mes

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
                $lastnum=$invoices->inv_number;
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
            
        } else {
                
            // el serial esta separado por barras. Hay que buscar
            // primero el ultimo numero
            // FORMATO FACTURA: SERIAL/000000000000001 (total debe medir $numLength)

            $invoices= Invoice::where([
                ['idcompany',$idcompany]
            ])
                ->select('inv_number')
                ->orderBy('inv_number','DESC')->first();

            if (is_null($invoices)) {
                // no hay ninguna factura aún

                if (strlen($serial)<1) {
                    // no tenemos numero de serie
                    $numprov='';
                    for ($n= 0;$n<($numLength-1);$n++) {
                        // rellenamos con ceros hasta 14 long
                        $numprov.='0';
                    }
                    $number=$numprov.'1';
                } else {
                    $numprov=substr($serial,0,3).'/';
                    for ($n= strlen($numprov);$n<($numLength-1);$n++) {
                        // rellenamos con ceros hasta 14 long
                        $numprov.='0';
                    }
                    $number=$numprov.'1';
                }

                return $number;


            } else {
                // obtenemos el ultimo valor grabado
                $lastnum=$invoices->inv_number;
                // comprobamos si es un numero
                $num=str_after($lastnum, $serial.'/');
                if (is_numeric($num)) {
                    // siguiente numero
                    $nextnum=$num+1;    
                } else {
                    // por algun motivo no es numerico
                    // es el primer numero ¿?
                    $nextnum=1;
                }

                // componemos el numero en funcion de si tiene numero
                // de serie o no
                if (strlen($serial)<1) {
                    // no tenemos numero de serie
                    $chain='00000000000000000000'.$nextnum;
                    $chain= substr($chain, -($numLength));
                    // chain debe medir 15
                    $number=$chain;
                    
                } else {
                    // tenemos numero de serie de longitud variable,
                    // por lo que llenamos chain de ceros y luego cortamos
                    // al tamaño adecuado
                    
                   $numprov=substr($serial,0,3).'/';
                   $chain='';
                    for ($n= strlen($numprov);$n<($numLength-1);$n++) {
                        // rellenamos con ceros hasta 14 long
                        $chain.='0';
                    }
                    $chain.=$nextnum;
                    // recortamos la cadena empezando por detrás
                    $chain=substr($chain,-($numLength-strlen($numprov)));
                    $number=$numprov.$chain;
                    
                }
                return $number;

            }             
            
        }
        
        
                           
        
    }
    
    /**
     * Esta función retorna el método de pago del cliente correspondiente al idcustomer
     * Si algo va mal retorna 0
     * @param type $idcustomer
     * @return int
     */
    private function getPaymentMethod($idcustomer=0) {
        
        try {
            $pmethod= Customer::find($idcustomer);
            if (is_null($pmethod) || $pmethod==false) return 0;
            // retorna el metodo de pago del cliente
            return $pmethod->idmethod;
        } catch (Exception $ex) {
            return 0;
        } catch (QueryException $quex) {
            return 0;
        }
        
    }
    
    
    /**
     * Esta función devuelve una fecha de pago, al recibir una forma de pago
     * y la fecha de la factura
     * @param type $paymentMethod
     * @param type $invoicedate
     * @return int
     */
    private function getExpiration($paymentMethod=0,$invoicedate) {
        
        
        // si el método de pago es cero (desconocido), devuelve la fecha de la 
        // factura como fecha de pago
        if ($paymentMethod==0) return $invoicedate;
        
        try {
            // obtenemos el metodo de pago
            $pmethod= PaymentMethod::find($paymentMethod);
            // obtenemos los días de aplazamiento
            $diff=$pmethod->diff;
            // obtenemos el día de pago
            $pday=$pmethod->payment_day;
            
            // convertimos a timestamp la fecha de la factura
            //$ddate= converterDateTime($invoicedate);
            $ddate=converterDateToDDBB($invoicedate);
            // le añadimos el aplazamiento
            //$ddate= date_add($ddate, $diff);
            $ddate=date('Y-m-d', strtotime($ddate. ' + '.$diff.' days'));
            
            // le añadimos dia de pago si procede
            if ($pday>0 && $pday > substr($ddate, 8,2)) {
                // si el dia de pago es posterior a la fecha de factura
                // solo hay que cambiar el dia por el dia de pago
                
                // cambiamos a dos cifras si procede
                if ($pday<10) $pday='0'.$pday;
                // cambiamos la fecha
                $ddate=substr($ddate,0,8).$pday;
            } elseif ($pday>0 && $pday < substr($ddate, 8,2)) {
                // si el dia de pago es anterior a la fecha de factura
                // hay que incrementar el mes y el año si procede
                
                // cambiamos a dos cifras si procede
                if ($pday<10) $pday='0'.$pday;
                // tomamos el mes y el año
                $month=substr($ddate,5,2);
                $year=substr($ddate,0,4);
                
                // incrementamos el mes
                if ($month<12) {
                    $month++;
                } else {
                    $month=1;
                    $year++;
                }
                // cambiamos a dos cifras si procede
                if ($month<10) $month='0'.$month;  
                
                // finalmente componemos la fecha
                $ddate=$year.'-'.$month.'-'.$pday;
            } 
                        
            return $ddate;
            
        } catch (Exception $ex) {
            return 0;
        } catch (QueryException $quex) {
            return 0;
        }
        
    }
    
    
    /**
     * Esta función retorna el tipo de iva y el porcentaje:
     * (1=superreducido, 2=reducido, 3=general)
     * devuelve 0 en caso de error
     * @param type $idiva
     * @return int
     */
    private function getIvaType($idiva=0) {
        
        // obtenemos el tipo de iva
        try {
            $iva= IvaRates::find($idiva);
            
            return array($iva->type,$iva->rate);
            
        } catch (Exception $ex) {
            // error
            return array(0,0);
            
        } catch (QueryException $quex) {
            // error
            return array(0,0);
        }        
    }
    
    
    
    /**
     * Esta función devuelve un String con el pie que va a ser incluído en
     * la factura. Si algo falla o no la encuentra, devuelve empty
     * Este texto se obtiene de las configuraciones de empresa, y suele contener
     * los datos registrales de la empresa
     */
    private function getInvoiceFooter($idcomp) {
        
        try {
            // buscar invoiceNote de la empresa
            $pie= Config::where([
                ['idcompany',$idcomp],
                ['name','invoiceNote']
            ])->first();
            
            if (is_null($pie)) return "";
            
            // devuelve el contenido de invoiceNote
            return $pie->value;
            
        } catch (Exception $ex) {
            // error
            return "";
        } catch (QueryException $quex) {
            // error
            return "";
        }
       
        
    }
    
    
    
    /**
     * Esta función fabrica un documento pdf suministrándole un html.
     * El formato será un documento utf-8 en formato A4 portrait
     * @param type $data
     * @return type
     */
    private function generatePdfDocument($data, $generated=true) {
        
        // creamos el directorio temporal
        $mpdf = new Mpdf([
            'tempDir' => __DIR__ . '/tmp',
            'mode' => 'utf-8', 
           'format' => [210, 297],
            'orientation' => 'P']);
        
        // generamos la fecha de emisión si true
        if ($generated===true) $mpdf->SetHeader('Emitido el '.date('d-m-Y H:i:s',time()));
                

        // generamos el html
        $mpdf->WriteHTML($data);

        // Output a PDF file directly to the browser
        $mpdf->Output();
        
        return;            
            
    }
    
    /**
     * Esta función fabrica un documento pdf suministrándole un html.
     * El formato será un documento utf-8 en formato A4 portrait
     * @param type $data
     * @return type
     */
    private function generatePdfDocumentInvoice($data, $pie=false, $generated=true) {
        
        // creamos el directorio temporal
        $mpdf = new Mpdf([
            'tempDir' => __DIR__ . '/tmp',
            'mode' => 'utf-8', 
            'format' => [210, 297],
            'orientation' => 'P']);
        
        // generamos la fecha de emisión si true
        if ($generated===true) $mpdf->SetHeader('Emitido el '.date('d-m-Y H:i:s',time()));
        
        if ($pie!==false) {
            $footer = array (
                'L' => array (
                    'content' => '',
                    'font-size' => 6,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color'=>'#000000'
                ),
                'C' => array (
                    'content' => $pie,
                    'font-size' => 6,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color'=>'#000000'
                ),
                'R' => array (
                    'content' => '',
                    'font-size' => 6,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color'=>'#000000'
                ),                
                'line' => 1
            );
            $mpdf->SetFooter($footer,'O');
        }

        // generamos el html
        $mpdf->WriteHTML($data);

        // Output a PDF file directly to the browser
        $mpdf->Output();
        
        return;            
            
    }    
    
}
