@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header"><h1>Listado de facturación por cliente</h1></div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>
                    
        <div class="x_panel">
            <div class="x_title">
              <h2>Listado de totales de facturas por cliente</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <p class="text-muted font-13 m-b-30">
              Emite el listado de sumatorio de facturas por clientes entre las fechas especificadas. En caso de no poner fechas, toma los 
              datos de todo el año actual.
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
                                            <option value="0">Todos</option>
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
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center">

                                        <button type="reset" class="btn btn-info" title="Borrar los datos del formulario" >
                                            <i class='fa fa-eraser'></i> Borrar</button>

                                        <button type="submit" class="btn btn-info" title="Mostrar listado por pantalla" formaction="{{url('showSumatoryInvoices')}}" >
                                            <i class="fa fa-eye"></i> Listar por Pantalla</button>
                                            
                                        <button type="submit" class="btn btn-info" style="margin-left: 40px;" 
                                                title="mostrar un PDF con la lista de facturas" formaction="{{url('sumatoryInvoicesPdf')}}" >
                                            <i class="fa fa-file-pdf-o"></i> Lista en PDF</button>                                            
                                    </td>                                
                                </tr>                                                                      
                            </tbody>
                        </table>
                      </div>
                  </div>
                </div>

                    <hr>
                    <br />

                    @if (isset($invoices) && count($invoices)>0)
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
                                        <th class="text-left" aria-controls="datatable-responsive" style="width: 25%" >Cliente</th>
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Base Exenta</th>
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Cuota Exenta</th>
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Base Superreducida.</th>
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Cuota Superreducida.</th>
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Base Reducida</th> 
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Cuota Reducida</th>
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Base General</th>  
                                        <th class="text-center" aria-controls="datatable-responsive" style="width: 7%;" >Cuota General</th>                                          
                                        <th class="text-right" aria-controls="datatable-responsive" style="width: 12%;" >Total Facturación</th>                               
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
                                                <td>{{$invoice['name']}}</td>
                                                <td class="text-right">{{number_format($invoice['base0'],2,',','.')}}</td>
                                                <td class="text-right">{{number_format($invoice['cuota0'],2,',','.')}}</td>
                                                <td class="text-right">{{number_format($invoice['base1'],2,',','.')}}</td>
                                                <td class="text-right">{{number_format($invoice['cuota1'],2,',','.')}}</td>
                                                <td class="text-right">{{number_format($invoice['base2'],2,',','.')}}</td>
                                                <td class="text-right">{{number_format($invoice['cuota2'],2,',','.')}}</td>
                                                <td class="text-right">{{number_format($invoice['base3'],2,',','.')}}</td>
                                                <td class="text-right">{{number_format($invoice['cuota3'],2,',','.')}}</td>                                            
                                                <td class="text-right">{{number_format($invoice['total'],2,',','.')}}</td> 
                                              </tr>
                                        @endforeach

                                      @isset($totals)
                                      <tr><td colspan="10"><hr></td> </tr>
                                          <tr>                                  
                                              <td class="text-right" style="font-weight: bold;"> Sumas...</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['base0'],2,',','.')}}</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['cuota0'],2,',','.')}}</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['base1'],2,',','.')}}</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['cuota1'],2,',','.')}}</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['base2'],2,',','.')}}</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['cuota2'],2,',','.')}}</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['base3'],2,',','.')}}</td>
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['cuota3'],2,',','.')}}</td>                                            
                                            <td class="text-right" style="font-weight: bold;">{{number_format($totals['total'],2,',','.')}}</td> 
                                          </tr>
                                       @endisset
                                  </tbody>
                              </table>
                            </div>


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
                    @endif
                </form>
              </div>										
        </div>
                   
            @if (isset($messageOK) && !is_null($messageOK))
            <div class="alert alert-success">{{$messageOK}}</div>
            @elseif (isset($messageWrong) && !is_null($messageWrong))
            <div class="alert alert-danger">{{$messageWrong}}</div>
            @endif
    </div>
</div>

@endsection

