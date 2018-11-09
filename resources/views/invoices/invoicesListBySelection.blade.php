@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header"><h1>Listado de facturas por selección</h1></div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>
                    
        <div class="x_panel">
            <div class="x_title">
              <h2>Listado de facturas seleccionados por formulario</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <p class="text-muted font-13 m-b-30">
                Localiza facturas a través de los parámetros de selección del formulario (opcionales). 
                Si se especifican varios parámetros, mostrará los resultados que coincidan con todos los parámetros elegidos.
              </p>

                <form method="post" class="form-group">
                    @csrf
                    <input type="hidden" name="companyid" value="{{Auth::guard('')->user()->idcompany}}">
                <div id="datatable-responsive_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                  <div class="row">
                      <div class="col-sm-12 col-md-12">
                        <table  class="table table-bordered dt-responsive nowrap dataTable no-footer dtr-inline" role="grid" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td>
                                        <label for="idcustomer">Cliente</label>
                                        <select name="idcustomer" class="form-control" title="Elija un cliente determinado, o todos">
                                            <option value="0">Seleccione...</option>
                                            @if (isset($customersSel) && !is_null($customersSel) && count($customersSel)>0)
                                              @foreach ($customersSel as $customer)
                                                  @if ($parameters['cust'] == $customer->id)
                                                      <option value="{{$customer->id}}" selected >{{$customer->customer_name}}</option>
                                                  @else
                                                      <option value="{{$customer->id}}">{{$customer->customer_name}}</option>
                                                  @endif                           
                                              @endforeach
                                            @endif
                                        </select>
                                    </td>                 
                                    <td>
                                        <label for="fechini">Fecha inicial</label>
                                        <input name="fechini" type="text" class="form-control" value="{{$parameters['fechini']}}" 
                                               title="Fecha inicial de búsqueda. Si no se especifica, el 01-01 del año en curso"
                                               maxlength="10" minlength="10" placeholder="filtro no obligatorio">
                                    </td>
                                    <td>
                                        <label for="fechfin">Fecha final</label>
                                        <input name="fechfin" type="text" class="form-control" value="{{$parameters['fechfin']}}" 
                                               title="Fecha final de búsqueda. Si no se especifica, el 31-12 del año en curso"
                                               maxlength="10" minlength="10" placeholder="filtro no obligatorio">
                                    </td>                                    
                                    <td>
                                        <label for="amount">Importe</label>
                                        <input name="amount" type="text" class="form-control" value="{{$parameters['amount']}}"
                                               title="Importe mínimo. Si no se especifica, será desde 0,00 euros"
                                               maxlength="20" placeholder="filtro no obligatorio">
                                    </td>
                                    <td>
                                        <label for="invnumber">Nº factura</label>
                                        <input name="invnumber" type="text" class="form-control" value="{{$parameters['invnumber']}}"
                                               title="Si se busca una factura concreta. Acepta búsquedas parciales."
                                               maxlength="20" placeholder="filtro no obligatorio">
                                    </td>                                                                               
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-center">

                                        <button type="reset" class="btn btn-info" title="Borrar los datos introducidos en formulario" >
                                            <i class='fa fa-eraser'></i> Borrar</button>

                                        <button type="submit" class="btn btn-info" title="Buscar facturas y listar en pantalla" 
                                                formaction="{{url('searchInvoices')}}" >
                                            <i class="fa fa-search-minus"></i> Buscar</button>


                                        <button type="submit" class="btn btn-info" style="margin-left: 40px;" 
                                                title="mostrar un PDF con la lista de facturas" formaction="{{url('searchInvoicesPdf')}}" >
                                            <i class="fa fa-search-minus"></i> Lista PDF</button>
                                    </td>                                      
                                </tr>                                                                      
                            </tbody>
                        </table>
                      </div>
                  </div>
                </div>

                </div>
                    <hr>
                    <br />

                    
                    
                    @if (isset($invoices) && !is_null($invoices) && count($invoices)>0)
                        <div class="x_panel">
                            <ul class="nav navbar-right panel_toolbox">
                             <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                             </li>
                             <li><a class="close-link"><i class="fa fa-close"></i></a>
                             </li>
                           </ul> 
                            <div class="clearfix"></div>
                          <div class="x_content">

                                <div id="datatable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                                  <div class="row">
                                      <div class="col-sm-6">
                                          <div class="dataTables_length" id="datatable_length">
                                              <label>Mostrar 
                                                  <select name="datatable_sel" id="datatable_sel" class="form-control" >
                                                      <option value="10">10</option>
                                                      <option value="25">25</option>
                                                      <option value="50">50</option>
                                                      <option value="100">100</option>
                                                  </select> entradas</label>
                                          </div>
                                      </div>
                                      <div class="col-sm-6">

                                      </div>
                                   </div>
                                </div>             


                          <div class="row">

                            <div class="col-sm-12 col-md-12">
                              <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap dataTable no-footer dtr-inline" role="grid" aria-describedby="datatable-responsive_info" style="width: 100%;" width="100%" cellspacing="0">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width:35%" >Cliente</th>
                                        <th class="text-center" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Fecha</th>
                                        <th class="text-center" tabindex="0" aria-controls="datatable-responsive" style="width: 15%;" >Número</th>
                                        <th class="text-right" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Importe</th>
                                        <th class="text-center" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Vencimiento</th>                                        
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 20%;"></th>
                                    </tr>
                                </thead> 
                                  <tbody id="bodytable">
                                    @foreach ($invoices as $invoice)
                                          @if ($loop->iteration > 10)
                                            {{-- utilizamos loop para el id del tr y para habilitar solamente los 10 primeros registros --}}
                                          <tr id="{{($loop->iteration)}}" style="display:none">                                               
                                          @else
                                          <tr id="{{($loop->iteration)}}">                                  
                                          @endif
                                            <td>{{$invoice->name}}</td>
                                            <td class="text-center">{{converterDate($invoice->inv_date)}}</td>
                                            <td class="text-center">{{$invoice->inv_number}}</td>
                                            <td class="text-right">{{$invoice->inv_total}}</td>
                                            <td class="text-center">{{converterDate($invoice->inv_expiration)}}</td>                                            
                                            <td class="text-center"><button type="submit" class="btn btn-info" formaction="{{url('showInvoice').'/'.$invoice->id}}"
                                              title="Pulse para editar este factura"><i class="fa fa-euro"></i> Ver Datos</button>
                                            <button type="submit" class="btn btn-success" formaction="{{url('showPdfInvoice').'/'.$invoice->id}}"
                                              title="Pulse para imprimir esta factura"><i class="fa fa-print"></i> Mostrar Pdf</button></td>                                               
                                          </tr>
                                      @endforeach
                                  </tbody>
                              </table>
                            </div>

                                  <div class="col-sm-12 col-md-12">
                                    <table  class="table table-bordered dt-responsive nowrap dataTable no-footer dtr-inline" role="grid" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td class="text-right" style="width:60%;">
                                                    <h3>Total del listado...</h3>
                                                </td>
                                                <td><h3>{{number_format($totalList,2,',','.')}} €</h3></td>
                                                <td>
                                                </td>                                    
                                            </tr>
                                        </tbody>
                                    </table>
                                  </div>

                                <div class="row">
                                @if (isset($invoices) && count($invoices)>0)
                                <div class="col-sm-5">
                                    <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                        <input type="hidden" id="count" value="{{count($invoices)}}">
                                        @if (count($invoices)>10)
                                        <p id="mostrando" name="mostrando">Mostrando 1 a 10 de {{count($invoices)}} entradas</p> 
                                        @else
                                        <p id="mostrando" name="mostrando">Mostrando 1 a {{count($invoices)}} de {{count($invoices)}} entradas</p> 
                                        @endif                                           
                                    </div>                        
                                </div>
                                <div class="col-sm-7">

                                    <div class="dataTables_paginate paging_simple_numbers" id="datatable_paginate">
                                        <ul class="pagination" id="pagination">
                                            <li class="paginate_button previous" id="datatable_previous">
                                                <a href="#" class="paginate_button_prev" aria-controls="datatable" data-dt-idx="0" tabindex="0" id="btprev">Anterior</a>
                                            </li>
                                            <li class="paginate_button "><a href="#" class="paginate_button" aria-controls="datatable" data-dt-idx="1" tabindex="0" id="bt1">1</a></li>
                                            <li class="paginate_button "><a href="#" class="paginate_button" aria-controls="datatable" data-dt-idx="2" tabindex="1" id="bt2">2</a></li>
                                            <li class="paginate_button "><a href="#" class="paginate_button" aria-controls="datatable" data-dt-idx="3" tabindex="2" id="bt3">3</a></li>
                                            <li class="paginate_button "><a href="#" class="paginate_button" aria-controls="datatable" data-dt-idx="4" tabindex="3" id="bt4">4</a></li>
                                            <li class="paginate_button "><a href="#" class="paginate_button" aria-controls="datatable" data-dt-idx="5" tabindex="4" id="bt5">5</a></li>
                                            <li class="paginate_button next" id="datatable_next">
                                                <a href="#" class="paginate_button_next" aria-controls="datatable" data-dt-idx="6" tabindex="0" id="btnext">Siguiente</a>
                                            </li>
                                        </ul>
                                    </div>            
                                 </div>
                                 @endif
                                </div>
                          </div>
                        </div>
                    @elseif (isset($invoices))
                    <div class="x_panel alert alert-warning">No hay ninguna factura en la selección efectuada</div>
                    @endif
                </form>
              </div>										
        </div>
        
            {{-- Zona de mensajes --}}        
            @if (isset($messageOK) && !is_null($messageOK))
            <div class="alert alert-success">{{$messageOK}}</div>
            @elseif (isset($messageWrong) && !is_null($messageWrong))
            <div class="alert alert-danger">{{$messageWrong}}</div>
            @endif
    </div>
</div>

@endsection

