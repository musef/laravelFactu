<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

use App\Customer;
use App\Work;
use App\Invoice;
use App\PaymentMethod;
use App\IvaRates;


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
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa';
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
        //die ($alb);
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
                $messageWrong='Error en base de datos obteniendo albaranes';

            }     
            
            try {   
                // obtenemos los clientes de la empresa
                $customers= Customer::where('idcompany',$idcomp)
                    ->get();                      
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error obteniendo la lista de los clientes de la empresa';
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa';
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
                $messageWrong='Error en base de datos obteniendo albaranes';

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
                                $number=$this->generateNextInvoiceNumber($idcompany, $invdate, '');
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
                            $number=$this->generateNextInvoiceNumber($idcompany, $invdate, '');
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

                            die ('***ac'.$c3);
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
                    $number=$this->generateNextInvoiceNumber($idcompany, $invdate, '');
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
                        $messageWrong='Error en base de datos grabando facturas';
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
                        $number=$this->generateNextInvoiceNumber($idcompany, $invdate, '');
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
                            $messageWrong='Error en base de datos grabando facturas';
                        } 
                    }
                    
                    if ($errors===false) $messageOK='Facturación realizada correctamente';
                    $works=null;
                }                 
            }
                
            try {   
                // obtenemos los clientes de la empresa
                $customers= Customer::where('idcompany',$idcomp)
                    ->get();
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error obteniendo la lista de los clientes de la empresa';
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa';
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
                ->get();                      
        } catch (Exception $ex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error obteniendo la lista de los clientes de la empresa';
        } catch (QueryException $quex) {
            // generamos un objeto en blanco
            $customers=null;
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa';
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
        //die ($alb);
        // CLIENTE
        // si no se da, serán todos
        if ($idcustomer<1) $idcustomer='%%';        
        
        // obtenemos la empresa del usuario
        $idcomp=Auth::guard('')->user()->idcompany;
        
        // verificamos que el usuario pertenece a la empresa
        if ($idcompany == $idcomp) {

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

            } catch (QueryException $quex) {

                // generamos un objeto en blanco
                $invoices=null;
                $customers=null;            
                $messageWrong='Error en base de datos obteniendo albaranes';

            }     
            
            try {   
                // obtenemos los clientes de la empresa
                $customers= Customer::where('idcompany',$idcomp)
                    ->get();                      
            } catch (Exception $ex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error obteniendo la lista de los clientes de la empresa';
            } catch (QueryException $quex) {
                // generamos un objeto en blanco
                $customers=null;
                $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa';
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
            $messageWrong='Error en base de datos obteniendo la lista de los clientes de la empresa';
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
                $messageWrong='Error en base de datos procesando el borrado de una factura';

            }            
        } else {
            $messageWrong='El usuario no pertenece a la empresa';
        }        
     
        
        return redirect()->action('InvoiceController@showInvoicesMenu',['mess'=>$messageOK]);
    }
    
    
    /**
     * Esta funcion obtiene el siguiente número de factura.
     * El formato del número que devuelve es el siguiente:
     * $serial(max 4 cifras) si lo tiene / año(4 cifras) - mes(2 cifras) - número consecutivo(relleno a 15) 
     * Ejemplo con serial una factura de octubre 2018: AB/201810000016
     * Ejemplo sin serial una factura de junio 2018: 201806000000023 
     * @param type $idcompany
     */
    private function generateNextInvoiceNumber($idcompany,$thisdate,$serial='') {
        
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
    
}
