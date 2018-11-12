@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        @if (isset($work->id) && $work->id > 0)
            <div class="card-header"><h1>Albarán</h1></div>
        @else
            <div class="card-header"><h1>Creación de nuevo albarán</h1></div>
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
                @if (isset($work->id) && $work->id > 0)
                <p>En este formulario se puede editar/modificar el albarán grabado</p>
                @else
                <p>Para crear un nuevo albarán rellene los datos y pulse en crear</p>
                @endif
              <br />
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf
                  
                @if (isset($work->id) && $work->id > 0) 
                <input type="hidden" name="workid" value="{{$work->id}}">
                @else
                <input type="hidden" name="workid" value="0">
                @endif
                <input type="hidden" name="companyid" value="{{Auth::guard('')->user()->idcompany}}">

                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-4 col-sm-4 col-xs-12" style="margin-top: 20px">
                      <label class="col-md-2 col-sm-2 col-xs-12" >Cliente: </label>
                      <div class="form-group col-md-10 col-sm-10 col-xs-12" >
                          <select name="customerid" id="customerid" class="form-group col-md-12 col-sm-12 col-xs-12" 
                                  style="height: 34px" onchange="submit()">
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
                          <input name='workaddress' id='worknumber' class="form-control" type="text" value="{{$customerSelected->customer_address}}" 
                                 readonly="readonly">
                      </div>
                    </div>
                    <div class="form-group col-md-4 col-sm-4 col-xs-12" style="margin-top: 20px">
                      <label class="col-md-2 col-sm-2 col-xs-12" >Localidad</label>
                      <div class="form-group col-md-10 col-sm-10 col-xs-12" >
                          <input name='workcity' id='worknumber' class="form-control" type="text" value="{{$customerSelected->customer_city}}" 
                                 readonly="readonly">
                      </div>
                    </div>                
                </div>
                
                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-4 col-sm-4 col-xs-4" style="margin-top: 20px">
                      <label class="col-md-2 col-sm-2 col-xs-12">Número</label>
                      <div class="col-md-10 col-sm-10 col-xs-12">
                          <input name='worknumber' id='worknumber' class="form-control" type="text" value="{{$work->work_number}}" 
                                 minlength="3" maxlength="15" readonly="readonly">
                      </div>
                    </div>
                    <div class="form-group col-md-4 col-sm-4 col-xs-4" style="margin-top: 20px">
                      <label class="control-label col-md-2 col-sm-2 col-xs-12">Fecha </label>
                      <div class="col-md-10 col-sm-10 col-xs-12">
                          <input name='workdate' id='workdate' class="form-control" type="text" value="{{$work->work_date}}" {{$disabled}}
                                 required maxlength="10" minlength="10" pattern="[0-3]{1}[0-9]{1}-[0-1]{1}[0-9]{1}-20[0-9]{2}"
                                 title="Introduzca una fecha con formato dd-mm-aaaa" >
                      </div>
                    </div>
                    <div class="form-group col-md-4 col-sm-4 col-xs-4" style="margin-top: 20px">
                      <label class="control-label col-md-2 col-sm-2 col-xs-12">Factura </label>
                      <div class="col-md-10 col-sm-10 col-xs-12">
                          <input name='workinvoice' id='workinvoice' class="form-control" type="text" value="{{$work->invoicenumber}}" 
                                 readonly="readonly" title="Facturado en factura ">
                      </div>
                    </div>
                </div>
                
                

                  @if ($customerSelected->id > 0)
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                  <label class="col-md-1 col-sm-1 col-xs-12" >Concepto </label>                  
                  <div class="col-md-12 col-sm-12 col-xs-12" >
                      <textarea name='workconcept' id='workconcept' class="form-control" minlength="5" maxlength="255" rows="10" {{$disabled}}
                                title="Concepto que describe la entrega o trabajo realizado" required="true">{{$work->work_text}}</textarea>
                  </div>
                </div>              
                <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="col-md-3 col-sm-3 col-xs-12">Cantidad </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='workqtt' id='workqtt' class="form-control" type="text" pattern="[0-9.,]{0,9}" {{$disabled}} 
                                 value="{{$work->work_qtt}}" title="cantidad a facturar" required >
                      </div>
                    </div>
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Precio </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='workprice' id='workprice' class="form-control" type="text" pattern="[0-9.,]{0,9}" {{$disabled}}
                                 value="{{$work->work_price}}" title="precio por unidad" required >
                      </div>
                    </div> 
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Tipo de IVA </label>
                          <select name="workiva" id="workiva" class="form-group col-md-9 col-sm-9 col-xs-12" {{$disabled}}
                                  style="height: 34px" >                              
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
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Total </label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                          <input name='worktotal' id='worktotal' class="form-control" type="text" pattern="[0-9.,]{0,9}" {{$disabled}}
                                 value="{{$work->work_total}}" title="Total" required >
                      </div>
                    </div>                  
                </div>                  
                  @else
                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                      <label class="col-md-1 col-sm-1 col-xs-12" >Concepto </label>                  
                      <div class="col-md-12 col-sm-12 col-xs-12" >
                          <textarea name='workconcept' id='workconcept' class="form-control"  rows="10"
                                    title="Debe seleccionar un cliente" placeholder="Debe seleccionar un cliente" disabled></textarea>
                      </div>
                    </div>                 
                    <div class="form-group col-md-12 col-sm-12 col-xs-12" >
                        <div class="form-group col-md-3 col-sm-3 col-xs-3">
                          <label class="col-md-3 col-sm-3 col-xs-12">Cantidad </label>
                          <div class="col-md-9 col-sm-9 col-xs-12">
                              <input name='workqtt' id='workqtt' class="form-control" type="text" 
                                     disabled title="Debe seleccionar un cliente" >
                          </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-3 col-xs-3">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Precio </label>
                          <div class="col-md-9 col-sm-9 col-xs-12">
                              <input name='workprice' id='workprice' class="form-control" type="text" 
                                     disabled title="Debe seleccionar un cliente" >
                          </div>
                        </div> 
                    <div class="form-group col-md-3 col-sm-3 col-xs-3">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Tipo de IVA </label>
                          <select name="workiva" id="workiva" class="form-group col-md-9 col-sm-9 col-xs-12" 
                                  style="height: 34px" onchange="submit()" disabled="">
                              <option value="0">Seleccione...</option>
                          </select>
                    </div>  
                        <div class="form-group col-md-3 col-sm-3 col-xs-3">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Total </label>
                          <div class="col-md-9 col-sm-9 col-xs-12">
                              <input name='worktotal' id='worktotal' class="form-control" type="text" 
                                     disabled title="Debe seleccionar un cliente" >
                          </div>
                        </div>                  
                    </div>                  
                  @endif
                 
                

                
                <div class="form-group col-md-10 col-sm-10 col-xs-12" style="margin-top: 20px">
                  <label class="col-md-2 col-sm-2 col-xs-12">Última actualizacion
                  </label>
                  <div class="col-md-5 col-sm-5 col-xs-12">
                    <input name='userupdate' class="date-picker form-control col-md-7 col-xs-12" readonly="readonly" type="text" value="{{$work->updated_at}}">
                  </div>
                </div>
               
                <div class="form-group">
                  <div class="col-md-4 col-sm-4 col-xs-12 text-center" >
                    @if (isset($work->id) && $work->id > 0)
                    <div class="col-md-8 col-sm-8 col-xs-12 text-right" >
                      <button type="submit" class="btn btn-success"  formaction="{{url('generateWork')}}" {{$disabled}}
                          title="Generar en fichero este albarán" onclick="return confirm('¿Seguro que desea generar fichero de este albarán?')">
                          <i class="fa fa-file"></i> Fichero PDF</button>                            
                      <button type="submit" class="btn btn-success"  formaction="{{url('showPdfWork')}}" {{$disabled}}
                              title="Mostrar albarán en PDF">
                          <i class="fa fa-eye"></i> Ver PDF</button>
                    </div>
                    @endif
                  </div>
                    
                  <div class="col-md-8 col-sm-8 col-xs-12 text-right">    
                        <button class="btn btn-warning" type="reset" title="Borrar los datos introducidos en el albarán" {{$disabled}}
                            onclick="return confirm('¿Seguro que desea borrar los datos introducidos en el albarán?')">
                            <i class="fa fa-eraser"></i> Borrar</button>
                        <button class="btn btn-info" type="button" title="calcular el total del albarán" {{$disabled}}
                                onclick="calculator()">
                            <i class="fa fa-calculator"></i> Calcular</button>
                        @if (isset($work->id) && $work->id > 0)
                            @if (strlen($work->invoicenumber)<1)
                            <button type="submit" class="btn btn-success"  formaction="{{url('changeWork')}}" 
                                title="Modificar los datos de este albarán" onclick="return confirm('¿Seguro que desea grabar los datos del albarán?')">
                                <i class="fa fa-save"></i> Modificar</button>                            
                            <button type="submit" class="btn btn-danger"  formaction="{{url('deleteWork')}}"
                                    title="Eliminar definitivamente este albarán"
                                    onclick="return confirm('¿Seguro que desea eliminar este albarán? La acción no podrá ser deshecha.')">
                                <i class="fa fa-save"></i> Eliminar</button>
                            @else
                            <button type="submit" class="btn btn-success"  formaction="{{url('changeWork')}}" disabled
                                title="No es posible modificar un albarán facturado">
                                <i class="fa fa-ban"></i> Modificar</button>                                                           
                            @endif
                        @elseif ($customerSelected->id > 0)
                        <button type="submit" class="btn btn-success"  formaction="{{url('recordNewWork')}}"
                                title="Grabar los datos de este albarán" onclick="return checkingForm()">
                                <i class="fa fa-save"></i> Grabar</button>
                        @else
                        <button type="submit" class="btn btn-success"  formaction="{{url('recordNewWork')}}"
                                title="Debe seleccionar un cliente para grabar" onclick="return checkingForm()" disabled>
                                <i class="fa fa-save"></i> Grabar</button>                              
                        @endif
                  </div>
                      
                  </div>
                </div>

              </form>
            </div>
            <script src="{{asset('js/works.js')}}"></script>
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
