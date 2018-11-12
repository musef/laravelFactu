@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header"><h1>Menú de facturación</h1></div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>
                    
        <div class="x_panel">
            <div class="x_title">
              <h2>Facturación automática de albaranes</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <p class="text-muted font-13 m-b-30">
                En función de la elección, factura automáticamente todos los albaranes pendientes o seleccionados, de 
                acuerdo con la selección efectuada en el formulario.
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
                                        <label for="invdate">Fecha factura</label>
                                        <input name="invdate" type="text" class="form-control" value="{{$parameters['invdate']}}" 
                                               title="Fecha de facturación. Por defecto, fecha de hoy"
                                               maxlength="10" minlength="10">
                                    </td>                                    
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
                                    <td>
                                        <label for="worknumber">Nº albarán</label>
                                        <input name="worknumber" type="text" class="form-control" value="{{$parameters['wknumber']}}"
                                               title="Si se busca un albarán concreto. Acepta búsquedas parciales."
                                               maxlength="20" placeholder="filtro no obligatorio">
                                    </td>                                     
                                    <td>
                                        <label for="format">Situación</label>
                                        <select name="format" class="form-control" title="Elija: factura por albarán o agrupados por cliente">                                            
                                            @if ($parameters['format']==1)
                                            <option value="0">Agrupados por cliente</option>
                                            <option value="1" selected >Factura por albarán</option>
                                            @else
                                            <option value="0" selected>Agrupados por cliente</option>
                                            <option value="1">Factura por albarán</option>                                             
                                            @endif
                                        </select>
                                    </td>                              
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center">

                                        <button type="reset" class="btn btn-info" title="Borrar los datos del formulario" >
                                            <i class='fa fa-eraser'></i> Borrar</button>

                                        <button type="submit" class="btn btn-info" title="Listar albaranes pendientes de facturar" formaction="{{url('worksList')}}" >
                                            <i class="fa fa-list-alt"></i> Listar</button>
                                            
                                        <button type="submit" class="btn btn-success" title="Facturar albaranes según selección del formulario" formaction="{{url('generateInvoices')}}" 
                                                onclick="return confirm ('¿Seguro que desea realizar la facturación seleccionada?')" >                                            
                                            <i class="fa fa-euro"></i> Facturar</button>                                            
                                    </td>                                
                                </tr>                                                                      
                            </tbody>
                        </table>
                      </div>
                  </div>
                </div>

                    <hr>
                    <br />

                    @if (isset($works) && !is_null($works) && count($works)>0)
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
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width:35%" >Nombre</th>
                                        <th class="text-center" tabindex="0" aria-controls="datatable-responsive" style="width: 8%;" >Fecha</th>
                                        <th class="text-center" tabindex="0" aria-controls="datatable-responsive" style="width: 13%;" >Número</th>
                                        <th class="text-center" tabindex="0" aria-controls="datatable-responsive" style="width: 21%;" >Concepto</th>
                                        <th class="text-right" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Importe</th>
                                        <th class="text-center" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;"></th>                                        
                                    </tr>
                                </thead> 
                                  <tbody id="bodytable">
                                    @foreach ($works as $work)
                                          @if ($loop->iteration > 10)
                                            {{-- utilizamos loop para el id del tr y para habilitar solamente los 10 primeros registros --}}
                                          <tr id="{{($loop->iteration)}}" style="display:none">                                               
                                          @else
                                          <tr id="{{($loop->iteration)}}">                                  
                                          @endif
                                            <td>{{$work->name}}</td>
                                            <td class="text-center">{{converterDate($work->work_date)}}</td>
                                            <td class="text-center">{{$work->work_number}}</td>
                                            <td class="text-left">{{substr($work->work_text,0,50)}}</td>
                                            <td class="text-right">{{$work->work_total}}</td>                                           
                                            <td class="text-center"><button type="submit" class="btn btn-info" formaction="{{url('editWork').'/'.$work->id}}"
                                              title="Pulse para editar este albarán"><i class="fa fa-wrench"></i> Ver albarán</button></td>
  
                                          </tr>
                                      @endforeach
                                      <tr><td colspan="7"> <button type="submit" class="btn btn-success" title="Facturar albaranes" formaction="{{url('generateInvoices')}}" 
                                                onclick="return confirm ('¿Seguro que desea realizar la facturación seleccionada?')" > 
                                            <i class="fa fa-euro"></i> Facturar</button> </td></tr>
                                  </tbody>
                              </table>
                            </div>


                            </div>


                                <div class="row">
                                @if (isset($works) && !is_null($works) && count($works)>0)
                                <div class="col-sm-5">
                                    <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                        <input type="hidden" id="count" value="{{count($works)}}">
                                        @if (count($works)>10)
                                        <p id="mostrando" name="mostrando">Mostrando 1 a 10 de {{count($works)}} entradas</p> 
                                        @else
                                        <p id="mostrando" name="mostrando">Mostrando 1 a {{count($works)}} de {{count($works)}} entradas</p> 
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
                   
            {{-- Zona de mensajes --}}        
            @if (isset($messageOK) && !is_null($messageOK))
            <div class="alert alert-success">{{$messageOK}}</div>
            @elseif (isset($messageWrong) && !is_null($messageWrong))
            <div class="alert alert-danger">{{$messageWrong}}</div>
            @endif
    </div>
</div>

@endsection

