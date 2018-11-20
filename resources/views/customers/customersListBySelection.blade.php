@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header"><h1>Listado de clientes por selección</h1></div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>
                    
        <div class="x_panel">
            <div class="x_title">
              <h2>Listado de clientes seleccionados por formulario</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <p class="text-muted font-13 m-b-30">
                Localiza clientes a través de los parámetros de selección del formulario (opcionales)
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
                                        <label for="name">Nombre</label>
                                        <input name="name" type="text" class="form-control" value="{{$parameters['name']}}" maxlength="15" placeholder="filtro no obligatorio">
                                    </td>
                                    <td>
                                        <label for="city">Localidad</label>
                                        <input name="city" type="text" class="form-control" value="{{$parameters['city']}}" maxlength="100" placeholder="filtro no obligatorio">
                                    </td>
                                    <td>
                                        <label for="zip">Código Postal</label>
                                        <input name="zip" type="text" class="form-control" value="{{$parameters['zip']}}" maxlength="25" placeholder="filtro no obligatorio">
                                    </td> 
                                    <td>
                                        <label for="paymentMethod">Forma de pago</label>
                                        <select name="paymentMethod" class="form-control">
                                            <option value="0">Todos</option>
                                         @if (isset($paymentMethods) && !is_null($paymentMethods))
                                          @foreach ($paymentMethods as $paymentMethod)
                                            @if ($parameters['selected']==$paymentMethod->id)
                                            <option value="{{$paymentMethod->id}}" selected="true">{{$paymentMethod->payment_method}}</option>
                                            @else
                                            <option value="{{$paymentMethod->id}}">{{$paymentMethod->payment_method}}</option>
                                            @endif
                                           @endforeach
                                          @endif
                                        </select>
                                    </td>                              
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-center">

                                        <button type="reset" class="btn btn-info" title="Borrar los datos del formulario" >
                                            <i class='fa fa-eraser'></i> Borrar</button>

                                        <button type="submit" class="btn btn-info" title="Buscar clientes" formaction="{{url('locateCustomersByOptions')}}" >
                                            <i class="fa fa-search-minus"></i> Buscar</button>
                                    </td>                                
                                </tr>                                                                      
                            </tbody>
                        </table>
                      </div>
                  </div>
                </div>

                    <hr>
                    <br />

                    @if (isset($customers) && count($customers)>0)
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
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width:40%" >Nombre</th>
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Nif</th>
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 15%;" >Localidad</th>
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 25%;" >Última actualización</th>
                                        <th class="sorting" tabindex="0" aria-controls="datatable-responsive" ></th>                                
                                    </tr>
                                </thead> 
                                  <tbody id="bodytable">
                                    @foreach ($customers as $customer)
                                          @if ($loop->iteration > 10)
                                            {{-- utilizamos loop para el id del tr y para habilitar solamente los 10 primeros registros --}}
                                          <tr id="{{($loop->iteration)}}" style="display:none">                                               
                                          @else
                                          <tr id="{{($loop->iteration)}}">                                  
                                          @endif
                                            <td>{{$customer->customer_name}}</td>
                                            <td>{{$customer->customer_nif}}</td>
                                            <td>{{$customer->customer_city}}</td>
                                            <td>{{$customer->updated_at}}</td>
                                            <td><button type="submit" class="btn btn-info" formaction="{{url('editCustomer').'/'.$customer->id}}"
                                              title="Pulse para editar este cliente"><i class="fa fa-calendar"></i> Ver cliente</button></td>
                                            <td><button type="submit" class="btn btn-danger" formaction="{{url('deleteCustomer').'/'.$customer->id}}"
                                              onclick="return confirm('¿Seguro que desea eliminar este cliente?')"
                                              title="Pulse para eliminar este cliente"><i class="fa fa-remove"></i> Eliminar </button></td>   
                                          </tr>
                                      @endforeach
                                  </tbody>
                              </table>
                            </div>


                            </div>


                                <div class="row">
                                @if (isset($customers) && count($customers)>0)
                                <div class="col-sm-5">
                                    <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                        <input type="hidden" id="count" value="{{count($customers)}}">
                                        @if (count($customers)>10)
                                        <p id="mostrando" name="mostrando">Mostrando 1 a 10 de {{count($customers)}} entradas</p> 
                                        @else
                                        <p id="mostrando" name="mostrando">Mostrando 1 a {{count($customers)}} de {{count($customers)}} entradas</p> 
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

