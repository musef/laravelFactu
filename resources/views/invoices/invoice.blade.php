@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        @if (isset($invoice->id) && $invoice->id > 0)
            <div class="card-header"><h1>Factura</h1></div>
        @else
            <div class="card-header"><h1>Creación de factura manual</h1></div>
        @endif


        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>

 
        @guest
        <div>
            <h1> Usuario no logueado. ACCESO NO AUTORIZADO</h1>
        </div>
        @else
        <div class="x_panel">
            <div class="x_content" style="display: block;">
                @if (isset($invoice->id) && $invoice->id > 0)
                <p>En este formulario se puede imprimir o eliminar la factura grabada</p>
                @else
                <p>Para crear una factura directa rellene los datos y pulse en crear</p>
                @endif
              <br />
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf
                  
                @if (isset($invoice->id) && $invoice->id > 0) 
                <input type="hidden" name="invoiceid" value="{{$invoice->id}}">
                @else
                <input type="hidden" name="invoiceid" value="0">
                @endif
                <input type="hidden" name="companyid" value="{{Auth::guard('')->user()->idcompany}}">

                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-4 col-sm-4 col-xs-12" style="margin-top: 20px">
                      <label class="col-md-2 col-sm-2 col-xs-12" >Cliente: </label>
                      <div class="form-group col-md-10 col-sm-10 col-xs-12" >
                          <select name="customerid" id="customerid" class="form-group col-md-12 col-sm-12 col-xs-12" 
                                disabled style="height: 34px" onchange="submit()" >
                              <option value="0">Seleccione...</option>
                              @if (isset($customers) && count($customers)>0)
                                @foreach ($customers as $customer)
                                    @if (isset($customerSelected) && $customerSelected->id == $customer->id)
                                        <option value="{{$customer->id}}" selected >{{$customer->customer_name}}</option>
                                    @else
                                        <option value="{{$customer->id}}">{{$customer->customer_name}}</option>
                                    @endif                           
                                @endforeach
                              @endif
                          </select>
                      </div>    
                    </div>                                                
                    <div class="form-group col-md-4 col-sm-4 col-xs-12" style="margin-top: 20px">
                      <label class="col-md-2 col-sm-2 col-xs-12" >Dirección</label>
                      <div class="form-group col-md-10 col-sm-10 col-xs-12" >
                          <input name='workaddress' id='worknumber' class="form-control" type="text" 
                                 value="{{$customerSelected->customer_address}}" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-4 col-sm-4 col-xs-12" style="margin-top: 20px">
                      <label class="col-md-2 col-sm-2 col-xs-12" >Localidad</label>
                      <div class="form-group col-md-10 col-sm-10 col-xs-12" >
                          <input name='workcity' id='worknumber' class="form-control" type="text" 
                                 value="{{$customerSelected->customer_city}}" readonly="readonly">
                      </div>
                    </div>                
                </div>
                
                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-4 col-sm-4 col-xs-4" style="margin-top: 20px">
                      <label class="col-md-2 col-sm-2 col-xs-12">Número</label>
                      <div class="col-md-10 col-sm-10 col-xs-12">
                          <input name='invnumber' id='worknumber' class="form-control" type="text" 
                                value="{{$invoice->inv_number}}" minlength="3" maxlength="15" readonly="readonly">
                      </div>
                    </div>
                    <div class="form-group col-md-4 col-sm-4 col-xs-4" style="margin-top: 20px">
                      <label class="control-label col-md-2 col-sm-2 col-xs-12">Fecha </label>
                      <div class="col-md-10 col-sm-10 col-xs-12">
                          <input name='invdate' id='workdate' class="form-control" type="text" value="{{converterDate($invoice->inv_date)}}"
                                 readonly maxlength="10" minlength="10" 
                                 title="Introduzca una fecha con formato dd-mm-aaaa" >
                      </div>
                    </div>
                    <div class="form-group col-md-4 col-sm-4 col-xs-4" style="margin-top: 20px">
                      <label class="control-label col-md-2 col-sm-2 col-xs-12">Factura </label>
                      <div class="col-md-10 col-sm-10 col-xs-12">
                          <input name='invoice' id='workinvoice' class="form-control" type="text" value="{{$invoice->inv_number}}" 
                                 readonly="readonly" title="Facturado en factura ">
                      </div>
                    </div>
                </div>
                
                @foreach ($works as $work)
                <hr class="form-group col-md-11 col-sm-11 col-xs-11">
                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-2 col-sm-2 col-xs-12">
                        <label class="col-md-5 col-sm-5 col-xs-12" >nº Albarán </label> 
                          <input name='worknumber' class="form-control" type="text"
                                 value="{{$work->work_number}}" title="nº albaran" readonly >                        
                    </div>
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="col-md-1 col-sm-1 col-xs-12" >Concepto </label>                  
                      <div class="col-md-12 col-sm-12 col-xs-12" >
                          <textarea name='workconcept' id='workconcept' class="form-control" minlength="5" maxlength="255" rows="1" readonly
                                    title="Concepto que describe la entrega o trabajo realizado" required="true">{{$work->work_text}}</textarea>
                      </div>
                    </div>
                </div>
                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-3 col-sm-3 col-xs-12">Cantidad </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='workqtt' id='workqtt' class="form-control" type="text" 
                                 value="{{number_format($work->work_qtt,2,',','.')}}" title="cantidad a facturar" readonly >
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Precio </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='workprice' id='workprice' class="form-control" type="text" 
                                 value="{{number_format($work->work_price,2,',','.')}}" title="precio por unidad" readonly >
                      </div>
                    </div> 
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Total </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='worktotal' id='worktotal' class="form-control" type="text"
                                 value="{{number_format($work->work_qtt * $work->work_price,2,',','.')}}" title="Total" readonly >
                      </div>
                    </div>                      
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Tipo de IVA </label>
                          <select name="workiva" id="workiva" class="form-group col-md-9 col-sm-9 col-xs-12" 
                                disabled  style="height: 34px" >                              
                              <option value="0">Seleccione...</option>
                              @if (isset($ivaRates) && count($ivaRates)>0)                              
                                @foreach ($ivaRates as $iva)
                                    @if ($iva->id == $work->idiva)
                                    <option value="{{$iva->rate}}" selected>{{$iva->iva_name}} {{$iva->rate}}%</option> 
                                    @else
                                    <option value="{{$iva->rate}}">{{$iva->iva_name}} {{$iva->rate}}%</option> 
                                    @endif
                                                             
                                @endforeach
                              @endif
                          </select>
                    </div>                  
                </div>                                            
                @endforeach
               
                <hr class="col-md-11 col-sm-11 col-xs-11 border-dark">

                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Base exenta </label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='base0' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_base0,2,',','.')}}" title="base a facturar" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Base Superreducida</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='base1' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_base1,2,',','.')}}" title="base a facturar" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Base Reducida</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='base2' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_base2,2,',','.')}}" title="base a facturar" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Base General</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='base3' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_base3,2,',','.')}}" title="base a facturar" readonly>
                      </div>
                    </div>
                </div>

                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Cuota exenta</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='cuota0' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_cuota0,2,',','.')}}" title="iva a facturar" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Cuota Superreducida</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='cuota1' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_cuota1,2,',','.')}}" title="iva a facturar" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Cuota Reducida</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='cuota2' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_cuota2,2,',','.')}}" title="iva a facturar" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-5 col-sm-5 col-xs-12">Cuota General</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='cuota3' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_cuota3,2,',','.')}}" title="iva a facturar" readonly>
                      </div>
                    </div>
                </div>
                               
                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-6 col-sm-6 col-xs-6">
                      <label class="col-md-3 col-sm-3 col-xs-12">Vencimiento </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='expiration' id='workqtt' class="form-control" type="text" 
                            value="{{converterDate($invoice->inv_expiration)}}" title="vencimiento de la factura" readonly>
                      </div>
                    </div>                      
                    <div class="form-group col-md-6 col-sm-6 col-xs-6">
                      <label class="col-md-3 col-sm-3 col-xs-12">Total Factura </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='total' id='workqtt' class="form-control" type="text" 
                            value="{{number_format($invoice->inv_total,2,',','.')}}" title="importe total de la factura" readonly>
                      </div>
                    </div>                    
                </div>
                
                <div class="form-group" style="margin-top: 20px;">               
                  <div class="col-md-11 col-sm-11 col-xs-12 text-right" > 
                    <button type="submit" class="btn btn-danger"  formaction="{{url('deleteInvoice')}}"
                            title="Eliminar definitivamente esta factura"
                            onclick="return confirm('¿Seguro que desea eliminar esta factura? La acción no podrá ser deshecha.')">
                        <i class="fa fa-exclamation-circle"></i> Eliminar</button>                      
                    <button type="submit" class="btn btn-success"  formaction="{{url('showPdfInvoice')}}"
                            title="Mostrar factura en PDF">
                        <i class="fa fa-eye"></i> Ver PDF</button>
                  </div>
                </div>

              </form>
            </div>
        </div>
        @endguest
        
            {{-- Zona de mensajes --}}        
            @if (isset($messageOK) && !is_null($messageOK))
            <div class="alert alert-success">{{$messageOK}}</div>
            @elseif (isset($messageWrong) && !is_null($messageWrong))
            <div class="alert alert-danger">{{$messageWrong}}</div>
            @endif      
    </div>
</div>
@endsection
